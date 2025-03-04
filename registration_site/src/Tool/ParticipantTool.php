<?php

namespace App\Tool;

use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Service\EmailService;

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
  public static function deleteParticipant(ParticipantEntity $participant): bool
  {
    if (!Tables::participants()->delete($participant)) {
      return false;
    }
    $event = Tables::events()->getForId($participant->event_id);
    if (!$event->hasActiveSignup()) {
      return true;
    }
    if (
      ($participant->event_workshop_1_id !== null)  || ($participant->event_workshop_2_id !== null)
    ) {
      ParticipantTool::checkParticipatingStatusForEvent($event);
    }
    return true;
  }

  /**
   * Checks the participating status for all workshops for all events that currently have an
   * active signup.
   *
   * @return void
   */
  public static function checkParticipatingStatusForAllEvents(): void
  {
    $events = Tables::events()->getAll();
    foreach ($events as $event) {
      self::checkParticipatingStatusForEvent($event);
    }
  }

  /**
   * Checks the participating status for all workshops in an event. If the event does not have
   * active signup nothing happens.
   *
   * @param EventEntity $event
   *
   * @return void
   */
  public static function checkParticipatingStatusForEvent(EventEntity $event): void
  {
    // ignore events that are not active
    if (!$event->hasActiveSignup()) {
      return;
    }
    $allEventworkshops = Tables::eventWorkshops()->getAllForEvent($event);
    $checkEventworkshops = [...$allEventworkshops];
    while (!empty($checkEventworkshops)) {
      $eventWorkshop = array_pop($checkEventworkshops);
      self::checkSecondWorkshop($eventWorkshop, $allEventworkshops, $checkEventworkshops);
    }
    foreach ($allEventworkshops as $eventWorkshop) {
      self::checkParticipatingEmails($event, $eventWorkshop);
    }
  }

  /**
   * The participant is leaving the first workshop. If a backup workshop is available, it will
   * become the first workshop.
   *
   * A cancellation email is sent to the user. Also
   * {@link self::checkParticipatingStatusForEvent} is called to check anyone else now is
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
    $participant->moveBackupToFirstWorkshop();
    if (Tables::participants()->save($participant)) {
      EmailService::sendCancellationEmail($user, $participant, $event, $eventWorkshop);
      self::checkParticipatingStatusForEvent($event);
      return true;
    }
    return false;
  }

  /**
   * The participant is leaving the backup workshop.
   *
   * A cancellation email is sent to the user. Also {@link checkParticipatingStatusForEvent} is
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
    $participant->clearBackupWorkshop();
    if (Tables::participants()->save($participant)) {
      EmailService::sendCancellationEmail($user, $participant, $event, $eventWorkshop);
      self::checkParticipatingStatusForEvent($event);
      return true;
    }
    return false;
  }

  /**
   * The participant is joining the first workshop. If the participant has already joined a first
   * workshop, the participant is removed and a call is made to
   * {@link self::checkParticipatingStatusForEvent} to check if any other participant is now
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
      if (
        Tables::participants()->removeFromWorkshop($participant, $participant->event_workshop_1_id)
      ) {
        self::addFirstWorkshop($user, $participant, $event, $eventWorkshop);
        self::checkParticipatingStatusForEvent($event);
      }
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
      if (
        Tables::participants()->removeFromWorkshop($participant, $participant->event_workshop_2_id)
      ) {
        self::addBackupWorkshop($user, $participant, $event, $eventWorkshop);
        ParticipantTool::checkParticipatingStatusForEvent($event);
      }
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
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop);
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
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop);
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

  /**
   * Checks if any of the participating participants is participating in the workshop as their
   * first choice. If they are, remove the backup workshop (if any).
   *
   * @param EventWorkshopEntity $eventWorkshop
   * @param array $allEventWorkshops The key is the id and the value the entity
   * @param array $checkEventWorkshops The keY is the id and the value the entity; this array gets
   *   updated with every second workshop that needs to be checked.
   *
   * @return void
   */
  private static function checkSecondWorkshop(
    EventWorkshopEntity $eventWorkshop,
    array $allEventWorkshops,
    array &$checkEventWorkshops
  ): void {
    $participants = Tables::participants()->getAllParticipatingForWorkshop($eventWorkshop);
    foreach ($participants as $participant) {
      // remove second workshop if the user is participating in the first workshop
      if (
        ($participant->event_workshop_1_id === $eventWorkshop->id) &&
        ($participant->event_workshop_2_id !== null)
      ) {
        // if the workshop is already in the array, nothing changes; else add it so it gets checked
        $checkEventWorkshops[$participant->event_workshop_2_id] =
          $allEventWorkshops[$participant->event_workshop_2_id];
        Tables::participants()->removeFromWorkshop($participant, $participant->event_workshop_2_id);
      }
    }
  }

  /**
   * For all participants that are participating in a workshop, check if they have been notified.
   * If not, send out an email.
   *
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   * @return void
   */
  private static function checkParticipatingEmails(
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop
  ): void {
    $participants = Tables::participants()->getAllParticipatingForWorkshop($eventWorkshop);
    foreach ($participants as $participant) {
      if ($participant->hasNotBeenNotified($eventWorkshop)) {
        $participant->notifyForWorkshop($eventWorkshop);
        Tables::participants()->save($participant);
        $user = Tables::users()->getForId($participant->user_id);
        EmailService::sendParticipatingEmail(
          $user,
          $participant,
          $event,
          $eventWorkshop,
          $participant->event_workshop_2_id === $eventWorkshop->id
        );
      }
    }
  }
}
