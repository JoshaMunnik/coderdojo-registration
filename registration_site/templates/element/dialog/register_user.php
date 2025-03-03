<?php

use App\Controller\AccountController;
use App\Model\View\Account\RegistrationViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var RegistrationViewModel $data
 * @var string $id
 */

?>
<?= $this->Styling->beginFormDialog($id, __('Register'), $data) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  RegistrationViewModel::EMAIL,
  [
    'label' => __('Email'),
    'type' => 'email',
    'required' => true
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::PASSWORD,
  [
    'label' => __('Password'),
    'type' => 'password',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::CONFIRM_PASSWORD,
  [
    'label' => __('Confirm password'),
    'type' => 'password',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::NAME,
  [
    'label' => __('Name'),
    'type' => 'text',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::PHONE,
  [
    'label' => __('Phone number (optional)'),
    'type' => 'text',
    'required' => false,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::MAILING_LIST,
  [
    'label' => __('Join mailing list'),
    'type' => 'checkbox',
    'required' => false,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  RegistrationViewModel::AGREE_TERMS,
  [
    'label' => __('Agree to terms and conditions'),
    'type' => 'checkbox',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->button(
  __('Register'),
  [
    'name' => AccountController::SUBMIT_REGISTER,
  ],
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
