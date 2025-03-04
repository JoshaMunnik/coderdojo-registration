<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Model\Value\Language;
use App\Model\View\IdViewModel;
use App\Model\View\Users\EditUserViewModel;
use App\Tool\FileTool;
use App\Tool\ParticipantTool;
use Cake\Http\Response;
use DateTime;
use DateTimeImmutable;

/**
 * {@link UsersController} manages the users.
 */
class UsersController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const EDIT = [self::class, 'edit'];
  public const REMOVE = [self::class, 'remove'];
  public const PARTICIPANTS = [self::class, 'participants'];
  public const REMOVE_PARTICIPANT = [self::class, 'remove-participant'];
  public const DOWNLOAD = [self::class, 'download'];
  public const ABSENT_PARTICIPANTS = [self::class, 'absent-participants'];
  public const REMOVE_ABSENT_PARTICIPANT = [self::class, 'remove-absent-participant'];

  #endregion

  #region public methods

  /**
   * Shows the list of users and their participants.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $this->set('users', Tables::users()->getAllWithParticipantsAndAbsentParticipants());
    return null;
  }

  /**
   * Processes the edit form (shown in the index page).
   *
   * @param string|null $id
   *
   * @return Response|null
   */
  public function edit(?string $id = null): ?Response
  {
    $viewData = $this->processEdit($id);
    $this->set('data', $viewData);
    return null;
  }

  /**
   * Handles the removal of a user.
   *
   * @return Response|null
   */
  public function remove(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $currentUser = $this->user();
    $user = Tables::users()->getForId($viewData->id);
    Tables::users()->deleteAndUpdateParticipants($user);
    ParticipantTool::checkParticipatingStatusForAllEvents();
    $this->success(__('User {0} removed', $user->name));
    if ($currentUser->id === $user->id) {
      return $this->redirect(AccountController::LOGOUT);
    }
    return $this->redirect(self::INDEX);
  }

  /**
   * Shows all participants for a user.
   *
   * @param $id
   *
   * @return Response|null
   */
  public function participants($id): ?Response
  {
    $user = Tables::users()->getForId($id);
    $participants = Tables::participants()->getAllForUserWithEventAndWorkshops($user);
    $this->set('user', $user);
    $this->set('participants', $participants);
    return null;
  }

  /**
   * Removes a participant.
   *
   * @return Response|null
   */
  public function removeParticipant(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $participant = Tables::participants()->getForId($viewData->id);
    Tables::participants()->delete($participant);
    ParticipantTool::checkParticipatingStatusForAllEvents();
    $this->success(__('Participant {0} removed', $participant->name));
    return $this->redirect(self::PARTICIPANTS, $participant->user_id);
  }

  /**
   * Shows all participants for a user.
   *
   * @param $id
   *
   * @return Response|null
   */
  public function absentParticipants($id): ?Response
  {
    $user = Tables::users()->getForId($id);
    $absents = Tables::absentParticipants()->getAllForUserWithEvent($user);
    $this->set('user', $user);
    $this->set('absents', $absents);
    return null;
  }

  /**
   * Removes a participant.
   *
   * @return Response|null
   */
  public function removeAbsentParticipant(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $absent = Tables::absentParticipants()->getForId($viewData->id);
    $event = Tables::events()->getForId($absent->event_id);
    Tables::absentParticipants()->delete($absent);
    ParticipantTool::checkParticipatingStatusForAllEvents();
    $this->success(__('Absent for event at {0} removed', $event->getEventDateAsText()));
    return $this->redirect(self::ABSENT_PARTICIPANTS, $absent->user_id);
  }

  /**
   * Downloads a CSV file with the workshops for an event.
   *
   * @param string $id
   *
   * @return Response
   */
  public function download(): Response {
    $users = Tables::users()->getAllWithParticipantsAndAbsentParticipants();
    $headers = [
      __('Email'),
      __('Name'),
      __('Phone'),
      __('Language'),
      __('Created'),
      __('Last visit'),
      __('Participants'),
      __('Absents'),
      __('Mailing list'),
      __('Administrator'),
    ];
    $data = [];
    foreach($users as $user) {
      $data[] = [
        $user->email,
        $user->name,
        $user->phone,
        Language::getName($user->language_id),
        $user->created->format('Y-m-d H:i:s'),
        $user->last_visit_date?->format('Y-m-d H:i:s') ?? '',
        count($user->participants),
        count($user->absent_participants),
        $user->mailing_list ? __('Yes') : '',
        $user->administrator ? __('Yes') : '',
      ];
    }
    $filename = FileTool::addDate('users.csv', new DateTimeImmutable());
    return $this->exportCsv($filename, $data, $headers);
  }

  #endregion

  #region private methods

  /**
   * Processes the edit form.
   *
   * @param string|null $id
   *
   * @return EditUserViewModel
   */
  private function processEdit(?string $id): EditUserViewModel
  {
    $viewData = new EditUserViewModel();
    if (!$this->isSubmit()) {
      if ($id) {
        $user = Tables::users()->getForId($id);
        $viewData->copyFromEntity($user);
      }
      return $viewData;
    }
    if ($viewData->patch($this->getRequest()->getData())) {
      /** @var UserEntity $user */
      if ($viewData->isNew()) {
        $user = Tables::users()->newEmptyEntity();
      }
      else {
        $user = Tables::users()->getForId($viewData->id);
      }
      $viewData->copyToEntity($user);
      if (Tables::users()->save($user)) {
        $this->redirectWithSuccess(
          self::INDEX,
          $viewData->id
            ? __('User {0} updated', $user->name)
            : __('User {0} created', $user->name)
        );
      }
      else {
        $this->error(__('Failed to save user to the database'));
      }
    }
    $viewData->password = '';
    return $viewData;
  }

  #endregion
}
