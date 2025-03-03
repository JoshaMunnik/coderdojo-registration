<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Tables;
use ArrayObject;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;

/**
 * This table encapsulates the workshops added for certain events.
 *
 * All queries will return the related workshop and its texts.
 *
 * @property WorkshopsTable $Workshops
 * @property EventsTable $Events
 */
class EventWorkshopsTable extends TableWithTimestamp
{
  #region fields

  public const PARTICIPANTS_1 = 'participants_1';
  public const PARTICIPANTS_2 = 'participants_2';

  #endregion

  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(EventWorkshopEntity::class);
    $this
      ->belongsTo(WorkshopsTable::getDefaultAlias())
      ->setForeignKey(EventWorkshopEntity::WORKSHOP_ID);
    $this
      ->belongsTo(EventsTable::getDefaultAlias())
      ->setForeignKey(EventWorkshopEntity::EVENT_ID);
    $this
      ->hasMany(self::PARTICIPANTS_1)
      ->setClassName(ParticipantsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_WORKSHOP_1_ID);
    $this
      ->hasMany(self::PARTICIPANTS_2)
      ->setClassName(ParticipantsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_WORKSHOP_2_ID);
  }

  public function beforeFind(
    EventInterface $event,
    Query $query,
    ArrayObject $options,
    $primary
  ): void {
    $query->contain([WorkshopsTable::getDefaultAlias()]);
  }

  #endregion

  #region public methods

  /**
   * Finds all workshops for a event. Combine workshop information and add
   * all participants for each workshop.
   *
   * @param string $eventId
   *
   * @return EventWorkshopEntity[] The key is the id of the event workshop.
   */
  public function getAllForEvent(string $eventId): array
  {
    return $this
      ->find('all')
      ->where([EventWorkshopEntity::EVENT_ID => $eventId])
      ->all()
      ->toList();
  }

  /**
   * Finds workshops for a event. Combine workshop information and add all participants.
   *
   * @param string $id
   *
   * @return EventWorkshopEntity The key is the id of the event workshop.
   */
  public function getForIdWithParticipants(string $id): EventWorkshopEntity
  {
    /** @var EventWorkshopEntity $eventWorkshop */
    $eventWorkshop = $this->get($id, contain: [self::PARTICIPANTS_1, self::PARTICIPANTS_2]);
    return $eventWorkshop;
  }

  /**
   * Gets an event workshop for an id.
   *
   * @param string $id
   *
   * @return EventWorkshopEntity
   *
   * @throws RecordNotFoundException
   */
  public function getForId(string $id): EventWorkshopEntity
  {
    /** @var EventWorkshopEntity $result */
    $result = $this->get($id);
    return $result;
  }

  /**
   * Gets a event workshop for an id including the related event.
   *
   * @param string $id
   *
   * @return EventWorkshopEntity
   *
   * @throws RecordNotFoundException
   */
  public function getForIdWithEvent(string $id): EventWorkshopEntity
  {
    return $this
      ->find('all')
      ->contain([
        EventsTable::getDefaultAlias()
      ])
      ->where([$this->prefix(EventWorkshopEntity::ID) => $id])
      ->first();
  }

  /**
   * Gets the total number of participants actual participating for the event. Participants waiting
   * in the queue are skipped.
   * .
   * @param string $eventId
   *
   * @return int
   */
  public function getTotalParticipating(string $eventId): int
  {
    $eventWorkshops = $this->getAllForEvent($eventId);
    $result = 0;
    foreach ($eventWorkshops as $eventWorkshop) {
      $result += min(
        $eventWorkshop->place_count, Tables::participants()->getCountForWorkshop($eventWorkshop->id)
      );
    }
    return $result;
  }

  #endregion
}
