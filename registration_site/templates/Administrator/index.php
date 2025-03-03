<?php

use App\Controller\AccountController;
use App\Controller\AdministratorController;
use App\Controller\EventsController;
use App\Controller\UserController;
use App\Controller\UsersController;
use App\Controller\WorkshopsController;
use App\Model\Constant\HtmlAction;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\ChangePasswordViewModel;
use App\Model\View\EditProfileViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditProfileViewModel $editProfileData
 * @var ChangePasswordViewModel $changePasswordData
 */

const EDIT_PROFILE = 'edit-profile';
const CHANGE_PASSWORD = 'change-password';

?>
<?= $this->Styling->title(__('Home for administrator')) ?>
<?= $this->element('messages') ?>
<nav class="cd-layout__row cd-layout__row--wrap">
  <?= $this->Styling->linkButton(
    __('Workshops'),
    $this->url([WorkshopsController::INDEX])
  ) ?>
  <?= $this->Styling->linkButton(
    __('Events'),
    $this->url([EventsController::INDEX])
  ) ?>
  <?= $this->Styling->linkButton(
    __('Users'),
    $this->url([UsersController::INDEX])
  ) ?>
  <?= $this->Styling->linkButton(
    __('Clear caches'),
    $this->url([AdministratorController::CLEAR_CACHE])
  ) ?>
  <?= $this->Styling->button(
    __('Edit profile'),
    ButtonColorEnum::PRIMARY,
    [
      HtmlAction::SHOW_DIALOG => '#'.EDIT_PROFILE,
    ],
  ) ?>
  <?= $this->Styling->button(
    __('Change password'),
    ButtonColorEnum::PRIMARY,
    [
      HtmlAction::SHOW_DIALOG => '#'.CHANGE_PASSWORD,
    ],
  ) ?>
  <?= $this->Styling->linkButton(__('Logout'), AccountController::LOGOUT) ?>
  <?= $this->Styling->linkButton(
    __('Participant page'),
    UserController::INDEX,
    ButtonColorEnum::SECONDARY
  ) ?>
</nav>
<?= $this->element(
  'dialog/edit_profile', ['data' => $editProfileData, 'id' => EDIT_PROFILE]
) ?>
<?= $this->element(
  'dialog/change_password', ['data' => $changePasswordData, 'id' => CHANGE_PASSWORD]
) ?>
