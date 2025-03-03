<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Model\View\IdViewModel;
use App\Model\View\Users\EditUserViewModel;
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
    ParticipantTool::checkAllEvents();
    $this->success(__('User {0} removed', $user->name));
    if ($currentUser->id === $user->id) {
      return $this->redirect(AccountController::LOGOUT);
    }
    return $this->redirect(self::INDEX);
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
