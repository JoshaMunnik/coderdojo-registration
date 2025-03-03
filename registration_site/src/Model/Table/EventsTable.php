<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Data\EventWithCountsData;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use DateTime;

/**
 * This table encapsulates the events in the system.
 *
 * @property EventWorkshopsTable $EventWorkshops
 * @property ParticipantsTable $Participants
 */
class EventsTable extends TableWithTimestamp
{
  #region private constants

  private const PARTICIPANT_COUNT = 'participant_count';
  private const WORKSHOP_COUNT = 'workshop_count';
  private const PLACE_COUNT = 'place_count';

  #endregion

  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(EventEntity::class);
    $this
      ->hasMany(EventWorkshopsTable::getDefaultAlias())
      ->setForeignKey(EventWorkshopEntity::EVENT_ID);
    $this
      ->hasMany(ParticipantsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::EVENT_ID);
  }

  #endregion

  #region public methods

  /**
   * Gets all events including additional information.
   *
   * @return EventWithCountsData[]
   */
  public function getAllWithCounts(): array
  {
    $events = $this
      ->find('all')
      ->all()
      ->toList();
    $totalParticipatingCount = $this
      ->find('list', valueField: self::PARTICIPANT_COUNT)
      ->select([
        EventEntity::ID,
        self::PARTICIPANT_COUNT => $this->find()->func()->count(
          $this->Participants->prefix(ParticipantEntity::ID)
        ),
      ])
      ->leftJoinWith(ParticipantsTable::getDefaultAlias())
      ->groupBy([$this->prefix(EventEntity::ID)])
      ->disableHydration()
      ->all()
      ->toArray();
    $workshopCount = $this
      ->find('list', valueField: self::WORKSHOP_COUNT)
      ->select([
        EventEntity::ID,
        self::WORKSHOP_COUNT => $this->find()->func()->count(
          $this->EventWorkshops->prefix(ParticipantEntity::ID)
        ),
      ])
      ->leftJoinWith(EventWorkshopsTable::getDefaultAlias())
      ->groupBy([$this->prefix(EventEntity::ID)])
      ->disableHydration()
      ->all()
      ->toArray();
    $placeCount = $this
      ->find('list', valueField: self::PLACE_COUNT)
      ->select([
        EventEntity::ID,
        self::PLACE_COUNT => $this->find()->func()->sum(
          $this->EventWorkshops->prefix(EventWorkshopEntity::PLACE_COUNT)
        ),
      ])
      ->leftJoinWith(EventWorkshopsTable::getDefaultAlias())
      ->groupBy([$this->prefix(EventEntity::ID)])
      ->disableHydration()
      ->all()
      ->toArray();
    $result = [];
    $now = new DateTime();
    /** @var EventEntity $event */
    foreach ($events as $event) {
      $participatingCount = $this->EventWorkshops->getTotalParticipating($event->id);
      $result[] = new EventWithCountsData(
        $event,
        $participatingCount,
        $totalParticipatingCount[$event->id] - $participatingCount,
        $workshopCount[$event->id],
        $placeCount[$event->id] ?? 0,
      );
    }
    return $result;
  }

  /**
   * Gets the event for an id. Throws an exception if the event does not exist.
   *
   * @param string $id
   *
   * @return EventEntity
   *
   * @throws RecordNotFoundException
   */
  public function getForId(string $id): EventEntity
  {
    /** @var EventEntity $result */
    $result = $this->get($id);
    return $result;
  }

  /**
   * @return EventEntity[]
   */
  public function getAll(): array
  {
    return $this->find('all')->all()->toList();
  }

  /**
   * Gets the next event, if any.
   *
   * @return EventEntity|null
   */
  public function getNextEvent(): ?EventEntity
  {
    /** @var EventEntity | null $result */
    $result = $this
      ->find()
      ->where([
        EventEntity::EVENT_DATE.' >=' => date('Y-m-d'),
      ])
      ->orderByAsc(EventEntity::EVENT_DATE)
      ->first();
    return $result;
  }

  /**
   * Anonymize all participants in the event:
   * - clear name
   * - remove reference to related user
   *
   * @param EventEntity $event
   *
   * @return void
   */
  public function anonymize(EventEntity $event): void
  {
    $this->Participants->updateQuery()
      ->set([
        ParticipantEntity::NAME => '',
        ParticipantEntity::USER_ID => null,
      ])
      ->where([
        ParticipantEntity::EVENT_ID => $event->id,
      ])
      ->execute();
  }

  /**
   * Gets all events that are pending.
   *
   * @return string[]
   */
  public function getPendingIds(): array
  {
    $ids = $this
      ->find('list')
      ->select([EventEntity::ID])
      ->where([
        EventEntity::EVENT_DATE.' >' => new DateTime(),
      ])
      ->disableHydration()
      ->all()
      ->toArray();
    return array_values($ids);
  }

  #endregion
}
