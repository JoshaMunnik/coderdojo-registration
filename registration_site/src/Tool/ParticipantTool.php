<?php

namespace App\Tool;

use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Service\EmailService;
use Cake\I18n\DateTime;
use Exception;

/**
 * Tool for participants. Some methods will send out emails.
 */
class ParticipantTool
{
  /**
   * Gets the position of the participant in a list of participants.
   *
   * @param ParticipantEntity[] $participants
   * @param ParticipantEntity $participant
   *
   * @return int Position (0-based) or -1 if not found
   */
  public static function getPosition(
    array $participants,
    ParticipantEntity $participant,
  ): int {
    for ($position = 0; $position < count($participants); $position++) {
      if ($participants[$position]->id === $participant->id) {
        return $position;
      }
    }
    return -1;
  }

  /**
   * Compares two participants based on their join date for a workshop. Assume that either the
   * first or backup workshop is the correct workshop.
   *
   * @param string $eventWorkshopId
   * @param ParticipantEntity $first
   * @param ParticipantEntity $second
   *
   * @return int
   */
  public static function compareForWorkshop(
    string $eventWorkshopId,
    ParticipantEntity $first,
    ParticipantEntity $second
  ): int {
    $firstDate = $first->event_workshop_1_id == $eventWorkshopId
      ? $first->event_workshop_1_join_date
      : $first->event_workshop_2_join_date;
    $secondDate = $second->event_workshop_1_id == $eventWorkshopId
      ? $second->event_workshop_1_join_date
      : $second->event_workshop_2_join_date;
    if ($firstDate === $secondDate) {
      return 0;
    }
    if ($firstDate === null) {
      return -1;
    }
    if ($secondDate === null) {
      return +1;
    }
    return $firstDate <=> $secondDate;
  }

  /**
   * Deletes participant and then checks the participating status of the workshops the
   * participating was participating in if the event the participant belongs to has still an active
   * signup.
   *
   * @param ParticipantEntity $participant
   *
   * @return bool
   */
  public static function deleteParticipant(ParticipantEntity $participant): bool {
    if (!Tables::participants()->delete($participant)) {
      return false;
    };
    $event = Tables::events()->getForId($participant->event_id);
    if (!$event->hasActiveSignup()) {
      return true;
    }
    if  ($participant->event_workshop_1_id !== null) {
      ParticipantTool::checkParticipatingStatusForWorkshop(
        $event, $participant->event_workshop_1_id
      );
    }
    if  ($participant->event_workshop_2_id !== null) {
      ParticipantTool::checkParticipatingStatusForWorkshop(
        $event, $participant->event_workshop_2_id
      );
    }
    return true;
  }

  /**
   * Checks the participating status for all workshops. If the event does not have an active
   * signup nothing happens.
   *
   * @param EventEntity $event
   *
   * @return void
   */
  public static function checkAllWorkshops(EventEntity $event): void {
    if (!$event->hasActiveSignup()) {
      return;
    }
    $workshops = Tables::eventWorkshops()->getAllForEvent($event->id);
    foreach ($workshops as $workshop) {
      self::checkParticipatingStatusForWorkshop($event, $workshop->id);
    }
  }

  /**
   * Checks the participating status for all workshops for all events.
   *
   * @return void
   */
  public static function checkAllEvents(): void {
    $events = Tables::events()->getAll();
    foreach($events as $event) {
      self::checkAllWorkshops($event);
    }
  }

  /**
   * Checks the participating status of every participating in a workshop.
   *
   * @param EventEntity $event
   * @param string $workshopId
   *
   * @return void
   */
  public static function checkParticipatingStatusForWorkshop(
    EventEntity $event,
    string $workshopId,
  ): void {
    $eventWorkshop = Tables::eventWorkshops()->getForIdWithParticipants($workshopId);
    $participants = Tables::participants()->getAllForWorkshop($workshopId);
    foreach ($participants as $participant) {
      $previousEventWorkshopId = self::checkParticipatingStatusForParticipant(
        $event,
        $eventWorkshop,
        $participants,
        $participant,
      );
      // participant left the backup workshop, check if other participant might have joined as
      // an active participant
      if ($previousEventWorkshopId != null) {
        self::checkParticipatingStatusForWorkshop($event, $previousEventWorkshopId);
      }
    }
  }

  /**
   * Checks if a participant is no longer in the waiting queue and is participating in a workshop.
   *
   * If a participant is now participating in workshop 1, remove them from workshop 2.
   *
   * Send out an email if no notification email was sent before.
   *
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   * @param ParticipantEntity[] $participants
   * @param ParticipantEntity $participant
   *
   * @return null|string When not null, the method returns an id of a backup workshop, the
   *   participant is no longer participating in.
   */
  public static function checkParticipatingStatusForParticipant(
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop,
    array $participants,
    ParticipantEntity $participant,
  ): ?string {
    // make sure the participant is still attached to a user
    if ($participant->user_id === null) {
      return null;
    }
    $position = self::getPosition($participants, $participant);
    if ($position < 0) {
      return null;
    }
    if (
      ($participant->event_workshop_1_id == $eventWorkshop->id) &&
      ($position < $eventWorkshop->place_count)
    ) {
      // notify user that participant is now participating (do this only once)
      if ($participant->event_workshop_1_notify_date == null) {
        try {
          $participant->event_workshop_1_notify_date = new DateTime();
          $user = Tables::users()->getForId($participant->user_id);
          EmailService::sendParticipatingEmail($user, $participant, $event, $eventWorkshop, false);
          Tables::participants()->save($participant);
        }
        catch (Exception $exception) {
          // ignore
        }
      }
      // if participant is now participating in workshop 1, they no longer need the backup workshop
      // so remove.
      if ($participant->event_workshop_2_id != null) {
        $previousEventWorkshopId = $participant->event_workshop_2_id;
        Tables::participants()->removeFromWorkshop(
          $participant, $participant->event_workshop_2_id
        );
        return $previousEventWorkshopId;
      }
    }
    // check and notify user that participant is now participating in the backup workshop
    // (do this only once)
    elseif (
      ($participant->event_workshop_2_id == $eventWorkshop->id) &&
      ($position <= $eventWorkshop->place_count) &&
      ($participant->event_workshop_2_notify_date == null)
    ) {
      try {
        $participant->event_workshop_2_notify_date = new DateTime();
        $user = Tables::users()->getForId($participant->user_id);
        EmailService::sendParticipatingEmail($user, $participant, $event, $eventWorkshop, true);
        Tables::participants()->save($participant);
      }
      catch (Exception $exception) {
        // ignore
      }
    }
    return null;
  }

  /**
   * The participant is leaving the first workshop. If a backup workshop is available, it will
   * become the first workshop.
   *
   * A cancellation email is sent to the user. Also
   * {@link self::checkParticipatingStatusForWorkshop} is* called to check anyone else now is
   * participating in the workshop.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   *
   * @return bool True on success, false on failure
   */
  public static function leaveFirstWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
  ): bool {
    $eventWorkshop = Tables::eventWorkshops()->getForId($participant->event_workshop_1_id);
    $event = Tables::events()->getForId($participant->event_id);
    $participant->event_workshop_1_id = $participant->event_workshop_2_id;
    $participant->event_workshop_1_join_date = $participant->event_workshop_2_join_date;
    $participant->event_workshop_1_notify_date = $participant->event_workshop_2_notify_date;
    $participant->event_workshop_2_id = null;
    $participant->event_workshop_2_join_date = null;
    $participant->event_workshop_2_notify_date = null;
    if (Tables::participants()->save($participant)) {
      EmailService::sendCancellationEmail($user, $participant, $event, $eventWorkshop);
      self::checkParticipatingStatusForWorkshop($event, $eventWorkshop->id);
      return true;
    }
    return false;
  }

  /**
   * The participant is leaving the backup workshop.
   *
   * A cancellation email is sent to the user. Also {@link checkParticipatingStatusForWorkshop} is
   * called to check anyone else now is participating in the workshop.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   *
   * @return bool True on success, false on failure
   */
  public static function leaveBackupWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
  ): bool {
    $eventWorkshop = Tables::eventWorkshops()->getForId($participant->event_workshop_2_id);
    $event = Tables::events()->getForId($participant->event_id);
    $participant->event_workshop_2_id = null;
    $participant->event_workshop_2_join_date = null;
    $participant->event_workshop_2_notify_date = null;
    if (Tables::participants()->save($participant)) {
      EmailService::sendCancellationEmail($user, $participant, $event, $eventWorkshop);
      self::checkParticipatingStatusForWorkshop($event, $eventWorkshop->id);
      return true;
    }
    return false;
  }

  /**
   * The participant is joining the first workshop. If the participant has already joined a first
   * workshop, the participant is removed and a call is made to
   * {@link self::checkParticipatingStatusForWorkshop} to check if any other participant is now
   * participating.
   *
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   * @return void
   */
  public static function joinFirstWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop
  ): void {
    if ($participant->event_workshop_1_id != null) {
      $previousWorkshopId = $participant->event_workshop_1_id;
      Tables::participants()->removeFromWorkshop($participant, $previousWorkshopId);
      self::addFirstWorkshop($user, $participant, $event, $eventWorkshop);
      ParticipantTool::checkParticipatingStatusForWorkshop($event, $previousWorkshopId);
    }
    else {
      self::addFirstWorkshop($user, $participant, $event, $eventWorkshop);
    }
  }

  /**
   * The participant is joining the backup workshop. If the participant has already joined a backup
   * workshop, the participant is removed and a call is made to
   * {@link self::checkParticipatingStatusForWorkshop} to check if any other participant is now
   * participating.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  public static function joinBackupWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop
  ): void {
    if ($participant->event_workshop_2_id != null) {
      $previousWorkshopId = $participant->event_workshop_2_id;
      Tables::participants()->removeFromWorkshop($participant, $previousWorkshopId);
      self::addBackupWorkshop($user, $participant, $event, $eventWorkshop);
      ParticipantTool::checkParticipatingStatusForWorkshop($event, $previousWorkshopId);
    }
    else {
      self::addBackupWorkshop($user, $participant, $event, $eventWorkshop);
    }
  }

  #endregion

  #region private methods

  /**
   * Adds the participant to the first workshop. Update the participant in the database and send
   * out an email.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  private static function addFirstWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop,
  ): void {
    Tables::participants()->addToFirstWorkshop($participant, $eventWorkshop);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $position = ParticipantTool::getPosition($participants, $participant);
    EmailService::sendJoinFirstWorkshopEmail(
      $user,
      $participant,
      $event,
      $eventWorkshop,
      $position
    );
  }

  /**
   * Adds the participant to the backup workshop. Update the participant in the database and send
   * out an email.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  private static function addBackupWorkshop(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop,
  ): void {
    Tables::participants()->addToBackupWorkshop($participant, $eventWorkshop);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $position = ParticipantTool::getPosition($participants, $participant);
    $firstWorkshop = Tables::eventWorkshops()->getForId($participant->event_workshop_1_id);
    EmailService::sendJoinBackupWorkshopEmail(
      $user,
      $participant,
      $event,
      $firstWorkshop,
      $eventWorkshop,
      $position
    );
  }
}
