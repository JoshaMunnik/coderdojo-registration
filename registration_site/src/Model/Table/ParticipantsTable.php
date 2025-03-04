<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
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
   * @param EventEntity $event
   *
   * @return ParticipantEntity[]
   */
  public function getAllForEventWithUser(EventEntity $event): array
  {
    return $this->find()
      ->contain([UsersTable::getDefaultAlias()])
      ->where([$this->prefix(ParticipantEntity::EVENT_ID) => $event->id])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * @param EventEntity $event
   *
   * @return ParticipantEntity[]
   */
  public function getAllParticipatingForEventWithUser(EventEntity $event): array
  {
    return $this->find()
      ->contain([UsersTable::getDefaultAlias()])
      ->where([
        $this->prefix(ParticipantEntity::EVENT_ID) => $event->id,
        // it is enough to check only for workshop 1, because workshop 2 is only set if workshop 1
        // is also set.
        $this->prefix(ParticipantEntity::EVENT_WORKSHOP_1_ID).' IS NOT' => null,
      ])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * Finds all participants for a user and event.
   *
   * @param UserEntity $user
   * @param EventEntity $event
   *
   * @return ParticipantEntity[] The participants for the user and event.
   */
  public function getAllForUserAndEvent(UserEntity $user, EventEntity $event): array
  {
    return $this->find()
      ->where([
        $this->prefix(ParticipantEntity::USER_ID) => $user->id,
        $this->prefix(ParticipantEntity::EVENT_ID) => $event->id,
      ])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * Finds all participants for a user. Include related event and workshops.
   *
   * @param UserEntity $user
   *
   * @return ParticipantEntity[] The participants for the user and event.
   */
  public function getAllForUserWithEventAndWorkshops(UserEntity $user): array
  {
    return $this->find()
      ->contain([
        EventsTable::getDefaultAlias(),
        self::WORKSHOP_1,
        self::WORKSHOP_2,
      ])
      ->where([
        $this->prefix(ParticipantEntity::USER_ID) => $user->id,
      ])
      ->orderBy($this->prefix(ParticipantEntity::CREATED))
      ->all()
      ->toList();
  }

  /**
   * Gets the number of participants for a workshop for a certain event.
   *
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return int
   */
  public function getCountForWorkshop(EventWorkshopEntity $eventWorkshop): int
  {
    return $this
      ->find('all')
      ->where([
        'OR' => [
          ParticipantEntity::EVENT_WORKSHOP_1_ID => $eventWorkshop->id,
          ParticipantEntity::EVENT_WORKSHOP_2_ID => $eventWorkshop->id,
        ],
      ])
      ->count();
  }

  /**
   * Gets all the participants for a workshop (either as first or second choice). The participants
   * are sorted on join date.
   *
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return ParticipantEntity[] The participants for the workshop.
   */
  public function getAllForWorkshop(EventWorkshopEntity $eventWorkshop): array
  {
    $participants = $this
      ->find('all')
      ->where([
        'OR' => [
          ParticipantEntity::EVENT_WORKSHOP_1_ID => $eventWorkshop->id,
          ParticipantEntity::EVENT_WORKSHOP_2_ID => $eventWorkshop->id,
        ],
      ])
      ->all()
      ->toList();
    usort(
      $participants,
      fn($first, $second) => ParticipantTool::compareForWorkshop(
        $eventWorkshop->id, $first, $second
      )
    );
    return $participants;
  }

  /**
   * Gets all the participants for a workshop (either as first or second choice) that are
   * participating (and not in the waiting queue).
   *
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return ParticipantEntity[]
   */
  public function getAllParticipatingForWorkshop(EventWorkshopEntity $eventWorkshop): array
  {
    $participants = $this->getAllForWorkshop($eventWorkshop);
    return array_slice($participants, 0, $eventWorkshop->place_count);
  }

  /**
   * Removes a participant from a workshop.
   *
   * @param ParticipantEntity $participant
   * @param string $eventWorkshopId
   *
   * @return bool
   */
  public function removeFromWorkshop(ParticipantEntity $participant, string $eventWorkshopId): bool
  {
    if ($participant->event_workshop_1_id === $eventWorkshopId) {
      $participant->clearFirstWorkshop();
    }
    if ($participant->event_workshop_2_id === $eventWorkshopId) {
      $participant->clearBackupWorkshop();
    }
    return $this->save($participant) !== false;
  }

  /**
   * Adds a participant to the first workshop by setting workshop 1 related fields
   * .
   * @param ParticipantEntity $participant
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return bool
   */
  public function addToFirstWorkshop(
    ParticipantEntity $participant,
    EventWorkshopEntity $eventWorkshop
  ): bool {
    $count = $this->getCountForWorkshop($eventWorkshop);
    $participant->event_workshop_1_id = $eventWorkshop->id;
    $participant->event_workshop_1_join_date = new DateTime();
    $participant->event_workshop_1_notify_date = $count < $eventWorkshop->place_count
      ? new DateTime()
      : null;
    return $this->save($participant) !== false;
  }

  /**
   * Adds a participant to the backup workshop by setting workshop 1 related fields
   * .
   * @param ParticipantEntity $participant
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return bool
   */
  public function addToBackupWorkshop(
    ParticipantEntity $participant,
    EventWorkshopEntity $eventWorkshop
  ): bool {
    $count = $this->getCountForWorkshop($eventWorkshop);
    $participant->event_workshop_2_id = $eventWorkshop->id;
    $participant->event_workshop_2_join_date = new DateTime();
    $participant->event_workshop_2_notify_date = $count < $eventWorkshop->place_count
      ? new DateTime()
      : null;
    return $this->save($participant) !== false;
  }

  /**
   * @param ParticipantEntity $participant
   * @param bool $checkin
   *
   * @return bool
   */
  public function checkin(ParticipantEntity $participant, bool $checkin = true): bool
  {
    $participant->checkin_date = $checkin ? new DateTime() : null;
    return $this->save($participant) !== false;
  }


  #endregion
}
