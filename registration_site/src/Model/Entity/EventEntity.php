<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;


/**
 * {@link EventEntity} encapsulates a single event in the database.
 *
 * @property DateTime $signup_date
 * @property DateTime $event_date
 * @property int $participant_type
 *
 * @property ParticipantEntity[] $participants
 * @property EventWorkshopEntity[] $event_workshops
 */
class EventEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const SIGNUP_DATE = 'signup_date';
  public const EVENT_DATE = 'event_date';
  public const PARTICIPANT_TYPE = 'participant_type';

  #endregion

  #region public methods

  /**
   * Gets the event date as text.
   */
  public function getEventDateAsText(): string
  {
    return $this->event_date->format('Y-m-d H:m');
  }

  /**
   * Checks if the current date is between the signup and event date.
   *
   * @return bool True if users can add participants to this event.
   */
  public function hasActiveSignup(): bool
  {
    $now = new DateTime();
    return $this->signup_date <= $now && $this->event_date > $now;
  }

  /**
   * Checks if the event is finished.
   *
   * @return bool True if the event is finished.
   */
  public function isFinished(): bool
  {
    $yesterday = new DateTime('-1 day');
    return $this->event_date <= $yesterday;
  }
}
