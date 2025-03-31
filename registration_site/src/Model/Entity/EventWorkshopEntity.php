<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use App\Model\Table\ParticipantsTable;
use App\Model\Tables;
use Cake\ORM\Entity;

/**
 * {@link EventWorkshopEntity} encapsulates a single event workshop in the database.
 *
 * @property string $event_id
 * @property string $workshop_id
 * @property int $place_count
 *
 * @property WorkshopEntity $workshop
 */
class EventWorkshopEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const EVENT_ID = 'event_id';
  public const WORKSHOP_ID = 'workshop_id';
  public const PLACE_COUNT = 'place_count';

  #endregion

  #region private fields

  /**
   * Cached participants for this workshop.
   *
   * @var ParticipantEntity[]|null
   */
  private array|null $m_participants = null;

  #endregion

  #region public methods

  /**
   * Gets the name of the workshop.
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->workshop?->getName() ?? '';
  }

  /**
   * Gets the description of the workshop.
   */
  public function getDescription(): string
  {
    return $this->workshop?->getDescription() ?? '';
  }

  /**
   * Checks if a participant is participating in the workshop.
   *
   * @param string $participantId Id of participant.
   *
   * @return bool True if the participant is participating in the workshop.
   */
  public function isParticipating(string $participantId): bool
  {
    $participants = Tables::participants()->getAllForWorkshop($this);
    foreach($participants as $participant) {
      if ($participant->id === $participantId) {
        return true;
      }
    }
    return false;
  }

  /**
   * Determines how many laptops are needed for the workshop by counting the number of participants
   * that do not have a laptop.
   *
   * @return int
   */
  public function getLaptopsNeededCount(): int
  {
    $participants = Tables::participants()->getAllParticipatingForWorkshop($this);
    $result = 0;
    foreach($participants as $participant) {
      if (!$participant->has_laptop) {
        $result++;
      }
    }
    return $result;
  }

  /**
   * Gets all the participants for this workshop. The first time this method is called,
   * it will load all the participants from the database using
   * {@link ParticipantsTable::getAllForWorkshop()}. The result is cached and returned on subsequent
   * calls.
   *
   * @return ParticipantEntity[]
   */
  public function getParticipants(): array {
    if ($this->m_participants === null) {
      $this->m_participants = Tables::participants()->getAllForWorkshop($this);
    }
    return $this->m_participants;
  }

  /**
   * Gets the participants position in the waiting list.
   *
   * @param ParticipantEntity $participant
   *
   * @return int The position in the waiting list (1, 2, etc.). 0 if the participant has a place.
   *   -1 if the participant is not in the list.
   */
  public function getWaitingPosition(
    ParticipantEntity $participant
  ): int
  {
    $participants = $this->getParticipants();
    for($index = 0; $index < count($participants); $index++) {
      if ($participants[$index]->id === $participant->id) {
        return max(0, $index + 1 - $this->place_count);
      }
    }
    return -1;
  }

  #endregion
}
