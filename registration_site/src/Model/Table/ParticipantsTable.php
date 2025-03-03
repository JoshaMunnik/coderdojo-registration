<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Tool\ParticipantTool;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\DateTime;
use Cake\Validation\Validator;

/**
 * This table encapsulates the participants for an event.
 *
 * @property UsersTable $Users
 * @property EventsTable $Events
 * @property EventWorkshopsTable $EventWorkshops
 */
class ParticipantsTable extends TableWithTimestamp
{
  #region fields

  public const WORKSHOP_1 = 'workshop_1';
  public const WORKSHOP_2 = 'workshop_2';

  #endregion

  #region cakephp callbacks

  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(ParticipantEntity::class);
    $this
      ->belongsTo(UsersTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::USER_ID);
    $this
      ->belongsTo(self::WORKSHOP_1)
      ->setClassName(EventWorkshopsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_WORKSHOP_1_ID);
    $this
      ->belongsTo(self::WORKSHOP_2)
      ->setClassName(EventWorkshopsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_WORKSHOP_2_ID);
    $this
      ->belongsTo(EventsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_ID);
  }

  /**
   * @inheritDoc
   */
  public function validationDefault(Validator $validator): Validator
  {
    $validator
      ->requirePresence([ParticipantEntity::NAME], 'create')
      ->notEmptyString(ParticipantEntity::NAME);
    return $validator;
  }

  #endregion

  #region public methods

  /**
   * Gets an entity for an id.
   *
   * @param string $id
   *
   * @return ParticipantEntity
   *
   * @throws RecordNotFoundException
   */
  public function getForId(string $id): ParticipantEntity
  {
    /** @var ParticipantEntity $result */
    $result = $this->get($id);
    return $result;
  }

  /**
   * @return ParticipantEntity[]
   */
  public function getAll(): array {
    return $this->find('all')->all()->toList();
  }

  /**
   * @param string $eventId The id of the event.
   *
   * @return ParticipantEntity[]
   */
  public function getAllForEventWithUser(string $eventId): array
  {
    return $this->find()
      ->contain([UsersTable::getDefaultAlias()])
      ->where([$this->prefix(ParticipantEntity::EVENT_ID) => $eventId])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * @param string $eventId The id of the event.
   *
   * @return ParticipantEntity[]
   */
  public function getAllParticipatingForEventWithUser(string $eventId): array
  {
    return $this->find()
      ->contain([UsersTable::getDefaultAlias()])
      ->where([
        $this->prefix(ParticipantEntity::EVENT_ID) => $eventId,
        $this->prefix(ParticipantEntity::EVENT_WORKSHOP_1_ID).' IS NOT' => null,
      ])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * Finds all participants for a user and event.
   *
   * @param string $userId
   * @param string $eventId
   *
   * @return ParticipantEntity[] The participants for the user and event.
   */
  public function getAllForUserAndEvent(string $userId, string $eventId): array
  {
    return $this->find()
      ->where([
        $this->prefix(ParticipantEntity::USER_ID) => $userId,
        $this->prefix(ParticipantEntity::EVENT_ID) => $eventId,
      ])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * Gets the number of participants for a workshop for a certain event.
   *
   * @param string $eventWorkshopId
   *
   * @return int
   */
  public function getCountForWorkshop(string $eventWorkshopId): int
  {
    return $this
      ->find('all')
      ->where([
        'OR' => [
          ParticipantEntity::EVENT_WORKSHOP_1_ID => $eventWorkshopId,
          ParticipantEntity::EVENT_WORKSHOP_2_ID => $eventWorkshopId,
        ],
      ])
      ->count();
  }

  /**
   * Gets all the participants for a workshop (either as first or second choice). The participants
   * are sorted on join date.
   *
   * @param string $eventWorkshopId
   *
   * @return ParticipantEntity[] The participants for the workshop.
   */
  public function getAllForWorkshop(string $eventWorkshopId): array
  {
    $participants = $this
      ->find('all')
      ->where([
        'OR' => [
          ParticipantEntity::EVENT_WORKSHOP_1_ID => $eventWorkshopId,
          ParticipantEntity::EVENT_WORKSHOP_2_ID => $eventWorkshopId,
        ],
      ])
      ->all()
      ->toList();
    usort(
      $participants,
      fn($first, $second) => ParticipantTool::compareForWorkshop($eventWorkshopId, $first, $second)
    );
    return $participants;
  }

  /**
   * Removes a participant from a workshop. Note that the positions of other participants are not
   * updated by this call.
   *
   * @param ParticipantEntity $participant
   * @param string $eventWorkshopId
   *
   * @return void
   */
  public function removeFromWorkshop(ParticipantEntity $participant, string $eventWorkshopId): void
  {
    if ($participant->event_workshop_1_id === $eventWorkshopId) {
      $participant->event_workshop_1_id = null;
      $participant->event_workshop_1_join_date = null;
      $participant->event_workshop_1_notify_date = null;
    }
    if ($participant->event_workshop_2_id === $eventWorkshopId) {
      $participant->event_workshop_2_id = null;
      $participant->event_workshop_2_join_date = null;
      $participant->event_workshop_2_notify_date = null;
    }
    $this->save($participant);
  }

  /**
   * Adds a participant to the first workshop by setting workshop 1 related fields
   * .
   * @param ParticipantEntity $participant
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  public function addToFirstWorkshop(
    ParticipantEntity $participant,
    EventWorkshopEntity $eventWorkshop
  ): void {
    $count = $this->getCountForWorkshop($eventWorkshop->id);
    $participant->event_workshop_1_id = $eventWorkshop->id;
    $participant->event_workshop_1_join_date = new DateTime();
    $participant->event_workshop_1_notify_date = $count < $eventWorkshop->place_count
      ? new DateTime()
      : null;
    $this->save($participant);
  }

  /**
   * Adds a participant to the backup workshop by setting workshop 1 related fields
   * .
   * @param ParticipantEntity $participant
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  public function addToBackupWorkshop(
    ParticipantEntity $participant,
    EventWorkshopEntity $eventWorkshop
  ): void {
    $count = $this->getCountForWorkshop($eventWorkshop->id);
    $participant->event_workshop_2_id = $eventWorkshop->id;
    $participant->event_workshop_2_join_date = new DateTime();
    $participant->event_workshop_2_notify_date = $count < $eventWorkshop->place_count
      ? new DateTime()
      : null;
    $this->save($participant);
  }

  #endregion
}
