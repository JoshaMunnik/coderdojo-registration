<?php

namespace App\Service;

use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Tool\LanguageTool;
use App\View\ApplicationView;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Mailer\Mailer;

/**
 * {@link EmailService} is a service to send the various emails.
 */
class EmailService
{
  #region public methods

  /**
   * Sends an email with the reset token to the user.
   *
   * @param UserEntity $user
   * @param string $token
   *
   * @return void
   */
  public static function sendResetEmailToken(UserEntity $user, string $token): void
  {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user, $token) {
        self::getMailer('reset_email')
          ->setTo($user->email, $user->name)
          ->setSubject(
            __('Reset Password for {0} registration site', Configure::read('Custom.eventName'))
          )
          ->setViewVars([
            'user' => $user,
            'token' => $token,
          ])
          ->deliver();
      }
    );
  }

  /**
   * Sends a welcome email to a newly registered user.
   *
   * @param UserEntity $user
   *
   * @return void
   */
  public static function sendRegistrationEmail(UserEntity $user): void
  {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user) {
        self::getMailer('registration')
          ->setTo($user->email, $user->name)
          ->setSubject(__('Welcome at the {0} registration site',
            Configure::read('Custom.eventName')))
          ->setViewVars([
            'user' => $user,
          ])
          ->deliver();
      }
    );
  }

  /**
   * Sends an email to the user when a related participant joins a workshop.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   * @param int $position Position (0-based) of the participant in the list of participants.
   *
   * @return void
   */
  public static function sendJoinFirstWorkshopEmail(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop,
    int $position
  ): void {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user, $participant, $position, $event, $eventWorkshop) {
        self::getMailer('join_first_workshop')
          ->setTo($user->email, $user->name)
          ->setSubject(
            __(
              'Registered for {0} at the {1} event',
              $eventWorkshop->getName(), Configure::read('Custom.eventName')
            )
          )
          ->setViewVars([
            'user' => $user,
            'participant' => $participant,
            'event' => $event,
            'eventWorkshop' => $eventWorkshop,
            'position' => $position,
          ])
          ->deliver();
      }
    );
  }

  /**
   * Sends an email to the user when a related participant joins a workshop.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $firstEventWorkshop
   * @param EventWorkshopEntity $backupEventWorkshop
   * @param int $position Position (0-based) of the participant in the list of participants.
   *
   * @return void
   */
  public static function sendJoinBackupWorkshopEmail(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $firstEventWorkshop,
    EventWorkshopEntity $backupEventWorkshop,
    int $position
  ): void {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user, $participant, $position, $event, $firstEventWorkshop, $backupEventWorkshop) {
        self::getMailer('join_backup_workshop')
          ->setTo($user->email, $user->name)
          ->setSubject(
            __(
              'Registered for {0} at the {1} event',
              $backupEventWorkshop->getName(), Configure::read('Custom.eventName')
            )
          )
          ->setViewVars([
            'user' => $user,
            'participant' => $participant,
            'event' => $event,
            'position' => $position,
            'firstEventWorkshop' => $firstEventWorkshop,
            'backupEventWorkshop' => $backupEventWorkshop,
          ])
          ->deliver();
      }
    );
  }

  /**
   * Sends an email when a participating that was in the waiting queue, is now participating.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   * @param bool $isBackup
   *
   * @return void
   */
  public static function sendParticipatingEmail(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop,
    bool $isBackup
  ): void {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user, $participant,$event, $eventWorkshop, $isBackup) {
        self::getMailer('now_participating')
          ->setTo($user->email, $user->name)
          ->setSubject(
            __(
              'Update for {0} at the {1} event',
              $eventWorkshop->getName(), Configure::read('Custom.eventName')
            )
          )
          ->setViewVars([
            'user' => $user,
            'participant' => $participant,
            'event' => $event,
            'eventWorkshop' => $eventWorkshop,
            'isBackup' => $isBackup,
          ])
          ->deliver();
      }
    );
  }

  /**
   * Sends an email when a participant is removed from a workshop.
   *
   * @param UserEntity $user
   * @param ParticipantEntity $participant
   * @param EventEntity $event
   * @param EventWorkshopEntity $eventWorkshop
   *
   * @return void
   */
  public static function sendCancellationEmail(
    UserEntity $user,
    ParticipantEntity $participant,
    EventEntity $event,
    EventWorkshopEntity $eventWorkshop
  ): void {
    if ($user->disable_email) {
      return;
    }
    LanguageTool::runForLanguage(
      $user->language_id,
      function () use ($user, $participant, $event, $eventWorkshop) {
        self::getMailer('removed_from_workshop')
          ->setTo($user->email, $user->name)
          ->setSubject(
            __(
              'Removed {0} from workshop {1}', $participant->name, $eventWorkshop->getName()
            )
          )
          ->setViewVars([
            'user' => $user,
            'participant' => $participant,
            'event' => $event,
            'eventWorkshop' => $eventWorkshop,
          ])
          ->deliver();
      }
    );
  }

  #endregion

  #region private methods

  /**
   * Gets a mailer configured for a certain template only using html.
   *
   * A language code is added to the template using an underscore as separator and the current
   * locale.
   *
   * @param string $template Template name without the language code.
   *
   * @return Mailer
   */
  private static function getMailer(string $template): Mailer
  {
    $result = new Mailer('default');
    $result
      ->setEmailFormat('html')
      ->viewBuilder()
      ->setClassName(ApplicationView::class)
      ->setTemplate($template.'_'.I18n::getLocale());
    return $result;
  }

  #endregion
}
