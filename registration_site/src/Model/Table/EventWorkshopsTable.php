<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\EventWorkshopWithEventEntity;
use App\Model\Entity\EventWorkshopWithParticipantsEntity;
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

  /**
   * Updates query before find.
   */
  public function beforeFind(
    EventInterface $event,
    Query $query,
    ArrayObject $options,
    $primary
  ): void {
    // always include workshop (which in turn will always include all texts)
    $query->contain([WorkshopsTable::getDefaultAlias()]);
  }

  #endregion

  #region public methods

  /**
   * Finds all workshops for an event.
   *
   * @param EventEntity $event
   *
   * @return EventWorkshopEntity[] The key is the id of the event workshop.
   */
  public function getAllForEvent(EventEntity $event): array
  {
    return $this
      ->find('list', valueField: fn($entity) => $entity)
      ->where([EventWorkshopEntity::EVENT_ID => $event->id])
      ->all()
      ->toArray();
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
   * @return EventWorkshopWithEventEntity
   *
   * @throws RecordNotFoundException
   */
  public function getForIdWithEvent(string $id): EventWorkshopWithEventEntity
  {
    return $this
      ->find('all')
      ->contain([
        EventsTable::getDefaultAlias()
      ])
      ->where([$this->prefix(EventWorkshopEntity::ID) => $id])
      ->first();
  }

  #endregion
}
