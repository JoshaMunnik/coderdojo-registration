<?php

namespace App\Lib\Controller;

use App\Controller\AccountController;
use App\Controller\UserController;
use Cake\Event\EventInterface;

/**
 * {@link AdministratorControllerBase} add a check for every action if the user is logged in and
 * is an administrator. It should be used as base class for administrator related controllers.
 */
class AdministratorControllerBase extends ApplicationControllerBase
{
  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function beforeFilter(EventInterface $event): void
  {
    parent::beforeFilter($event);
    $user = $this->user();
    if (!$user) {
      $this->Flash->error(__('You must be logged in to access this page.'));
      $this->redirect(AccountController::LOGIN);
    }
    else {
      if (!$user->administrator) {
        $this->Flash->error(__('You do not have the right credentials to access this page.'));
        $this->redirect(UserController::INDEX);
      }
    }
  }

  #endregion
}
