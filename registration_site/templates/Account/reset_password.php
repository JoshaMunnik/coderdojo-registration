<?php

use App\Model\View\Account\ResetPasswordViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ResetPasswordViewModel $resetPasswordData
 * @var array $backAction
 */

?>
<?= $this->Styling->title(__('Reset password')) ?>
<?= $this->element('messages') ?>
<?= $this->Form->create($resetPasswordData, ['templates' => 'form_styles']) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  ResetPasswordViewModel::NEW_PASSWORD,
  [
    'label' => __('New password'),
    'type' => 'password',
    'required' => true
  ],
) ?>
<?= $this->Form->control(
  ResetPasswordViewModel::CONFIRM_PASSWORD,
  [
    'label' => __('Confirm password'),
    'type' => 'password',
    'required' => true
  ],
) ?>
<?= $this->Styling->beginFormButtons() ?>
<?= $this->Form->button(__('Update'), ['type' => 'submit']) ?>
<?= $this->Styling->endFormButtons() ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Form->end() ?>
