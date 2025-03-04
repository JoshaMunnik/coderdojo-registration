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

/**
 * {@link UsersController} manages the users.
 */
class UsersController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const EDIT = [self::class, 'edit'];
  public const REMOVE = [self::class, 'remove'];

  #endregion

  #region public methods

  /**
   * Shows the list of users and their participants.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $this->set('users', Tables::users()->getAllWithParticipants());
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
   * Download a CSV file with the participants for an event.
   *
   * @param string $id
   *
   * @return Response
   */
  public function download(string $id): Response
  {
    $event = Tables::events()->getForId($id);
    $participants = Tables::participants()->getAllForEventWithUser($event);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($event);
    $headers = [
      __('Email'),
      __('Name'),
      __('Phone'),
      __('Language'),
      __('Created'),
      __('Last visit'),
      __('Participants'),
      __('Mailing list'),

    ];
    $data = [];
    foreach ($participants as $participant) {
      $data[] = [
        $participant->name,
        $participant->user?->email ?? '-',
        $participant->user?->name ?? '-',
        $this->getWorkshopInformation(
          $participant, $participant->event_workshop_1_id, $eventWorkshops
        ),
        $this->getWorkshopInformation(
          $participant, $participant->event_workshop_2_id, $eventWorkshops
        ),
        $participant->user != null ? Language::getName($participant->user->language_id) : '',
        $participant->has_laptop ? __('yes') : '',
        $this->getParticipatedStatus($event, $participant, $eventWorkshops),
      ];
    }
    return $this->exportCsv(
      FileTool::addDate('participants.csv', $event->event_date->toNative()), $data, $headers
    );
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
