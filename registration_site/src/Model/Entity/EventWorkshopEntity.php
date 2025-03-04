<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use App\Model\Tables;
use Cake\ORM\Entity;

/**
 * {@link EventWorkshopEntity} encapsulates a single event workshop in the database.
 *
 * @property string $event_id
 * @property string $workshop_id
 * @property int $place_count
 *
 * @property EventEntity $event
 * @property WorkshopEntity $workshop
 * @property ParticipantEntity[] $participants_1
 * @property ParticipantEntity[] $participants_2
 */
class EventWorkshopEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const EVENT_ID = 'event_id';
  public const WORKSHOP_ID = 'workshop_id';
  public const PLACE_COUNT = 'place_count';

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
   * Gets the participants position in the waiting list.
   *
   * @param ParticipantEntity $participant
   *
   * @return int The position in the waiting list (1, 2, etc.). 0 if the participant has a place.
   *   -1 if the participant is not in the list.
   */
  public function getWaitingPosition(ParticipantEntity $participant): int
  {
    $participants = Tables::participants()->getAllForWorkshop($this);
    for($index = 0; $index < count($participants); $index++) {
      if ($participants[$index]->id === $participant->id) {
        return max(0, $index + 1 - $this->place_count);
      }
    }
    return -1;
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

  #endregion

  #region private methods

  #endregion
}
