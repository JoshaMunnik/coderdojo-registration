<?php

use App\Controller\AccountController;
use App\Model\View\Account\ForgotPasswordViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ForgotPasswordViewModel $data
 * @var string $id
 */

?>
<?= $this->Styling->beginFormDialog($id, __('Forgot password'), $data, AccountController::LOGIN) ?>
<?= $this->Styling->textBlock(
  __('Please enter your email address. An email will be sent with instructions on how to reset your password.')
) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  ForgotPasswordViewModel::EMAIL,
  [
    'label' => __('Email'),
    'type' => 'email',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Send email'),
  [
    'name' => AccountController::SUBMIT_FORGOT_PASSWORD,
  ],
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
