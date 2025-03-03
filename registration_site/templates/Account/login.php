<?php

use App\Controller\AccountController;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\Account\ForgotPasswordViewModel;
use App\Model\View\Account\LoginViewModel;
use App\Model\View\Account\RegistrationViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ?LoginViewModel $loginData
 * @var ?ForgotPasswordViewModel $forgotPasswordData
 * @var ?RegistrationViewModel $registrationData
 */

$loginData ??= new LoginViewModel();
$forgotPasswordData ??= new ForgotPasswordViewModel();
$registrationData ??= new RegistrationViewModel();

?>
<?= $this->Styling->title(__('Login')) ?>
<?= $this->element('messages') ?>
<?= $this->createForm($loginData, AccountController::LOGIN) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  LoginViewModel::EMAIL,
  [
    'label' => __('Email'),
    'type' => 'email',
    'required' => true
  ],
) ?>
<?= $this->Form->control(
  LoginViewModel::PASSWORD,
  [
    'label' => __('Password'),
    'type' => 'password',
    'required' => true,
  ],
) ?>
<?= $this->Form->control(
  LoginViewModel::REMEMBER_ME,
  [
    'label' => __('Keep me logged in.'),
    'type' => 'checkbox',
  ],
) ?>
<?= $this->Styling->beginFormButtons() ?>
<?= $this->Form->button(__('Sign in'), ['type' => 'submit']) ?>
<?= $this->Styling->endFormButtons() ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Form->end() ?>
<div>
  <?= $this->Styling->textButton(
    __('Forgot your password?'),
    [
      'data-uf-show-dialog' => '#forgot-password',
    ],
  ) ?>
</div>
<div>
  <?= $this->Styling->button(
    __('Don\'t have an account yet?'),
    ButtonColorEnum::PRIMARY,
    [
      'data-uf-show-dialog' => '#register',
    ],
  ) ?>
</div>
<?= $this->element(
  'dialog/forgot_password',
  [
    'data' => $forgotPasswordData,
    'id' => 'forgot-password'
  ]
) ?>
<?= $this->element(
  'dialog/register_user',
  [
    'data' => $registrationData,
    'id' => 'register'
  ]
) ?>
