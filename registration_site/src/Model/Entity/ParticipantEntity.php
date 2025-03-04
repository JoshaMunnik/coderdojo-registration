<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * {@link ParticipantEntity} encapsulates a participant in the database.
 * *
 * @property string $user_id
 * @property string $event_id
 * @property string $name
 * @property DateTime $registration_date
 * @property string|null $event_workshop_1_id
 * @property DateTime|null $event_workshop_1_join_date
 * @property DateTime|null $event_workshop_1_notify_date
 * @property string|null $event_workshop_2_id
 * @property DateTime|null $event_workshop_2_join_date
 * @property DateTime|null $event_workshop_2_notify_date
 * @property bool $can_leave
 * @property bool $has_laptop
 * @property DateTime|null $checkin_date
 *
 * @property UserEntity|null $user
 * @property EventEntity $event
 * @property EventWorkshopEntity|null $workshop_1
 * @property EventWorkshopEntity|null $workshop_2
 */
class ParticipantEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const USER_ID = 'user_id';
  public const EVENT_ID = 'event_id';
  public const NAME = 'name';
  public const REGISTRATION_DATE = 'registration_date';
  public const EVENT_WORKSHOP_1_ID = 'event_workshop_1_id';
  public const EVENT_WORKSHOP_1_JOIN_DATE = 'event_workshop_1_join_date';
  public const EVENT_WORKSHOP_1_NOTIFY_DATE = 'event_workshop_1_notify_date';
  public const EVENT_WORKSHOP_2_ID = 'event_workshop_2_id';
  public const EVENT_WORKSHOP_2_JOIN_DATE = 'event_workshop_2_join_date';
  public const EVENT_WORKSHOP_2_NOTIFY_DATE = 'event_workshop_2_notify_date';
  public const CAN_LEAVE = 'can_leave';
  public const HAS_LAPTOP = 'has_laptop';
  public const CHECKIN_DATE = 'checkin_date';

  #endregion

  #region public methods

  /**
   * Copies the backup workshop to the first workshop and clears the backup workshop.
   *
   * @return void
   */
  public function moveBackupToFirstWorkshop(): void
  {
    $this->event_workshop_1_id = $this->event_workshop_2_id;
    $this->event_workshop_1_join_date = $this->event_workshop_2_join_date;
    $this->event_workshop_1_notify_date = $this->event_workshop_2_notify_date;
    $this->clearBackupWorkshop();
  }

  /**
   * Clears all backup workshop fields.
   *
   * @return void
   */
  public function clearBackupWorkshop(): void {
    $this->event_workshop_2_id = null;
    $this->event_workshop_2_join_date = null;
    $this->event_workshop_2_notify_date = null;
  }

  /**
   * Clears all first workshop fields.
   *
   * @return void
   */
  public function clearFirstWorkshop(): void {
    $this->event_workshop_1_id = null;
    $this->event_workshop_1_join_date = null;
    $this->event_workshop_1_notify_date = null;
  }

  /**
   * Checks if the participant is participating in any of the workshops (and not in a waiting
   * queue).
   *
   * @param EventWorkshopEntity[] $eventWorkshops
   *
   * @return bool
   */
  public function isParticipating(array $eventWorkshops): bool
  {
    return
      (
        ($this->event_workshop_1_id != null) &&
        ($eventWorkshops[$this->event_workshop_1_id]->getWaitingPosition($this) === 0)
      ) ||
      (
        ($this->event_workshop_2_id != null) &&
        ($eventWorkshops[$this->event_workshop_2_id]->getWaitingPosition($this) === 0)
      );
  }

  /**
   * Checks if the participant has not been notified yet for a certain workshop.
   *
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return bool True if the participant joined the workshop but has not been notified yet.
   */
  public function hasNotBeenNotified(EventWorkshopEntity $eventWorkshop): bool
  {
    return
      (
        ($this->event_workshop_1_notify_date === null) &&
        ($this->event_workshop_1_id === $eventWorkshop->id)
      )
      ||
      (
        ($this->event_workshop_2_notify_date === null) &&
        ($this->event_workshop_2_id === $eventWorkshop->id)
      );
  }

  /**
   * Updates the notify date for a workshop.
   *
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  public function notifyForWorkshop(EventWorkshopEntity $eventWorkshop): void
  {
    if ($this->event_workshop_1_id === $eventWorkshop->id) {
      $this->event_workshop_1_notify_date = new DateTime();
    }
    if ($this->event_workshop_2_id === $eventWorkshop->id) {
      $this->event_workshop_2_notify_date = new DateTime();
    }
  }

  #endregion

}
