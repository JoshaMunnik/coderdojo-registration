<?php

use App\Lib\Controller\ApplicationControllerBase;
use App\Model\View\ChangePasswordViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ChangePasswordViewModel $data
 * @var string $id
 */

?>
<?= $this->Styling->beginFormDialog($id, __('Change your password'), $data) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  ChangePasswordViewModel::CURRENT_PASSWORD,
  [
    'label' => __('Current password'),
    'type' => 'password',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  ChangePasswordViewModel::NEW_PASSWORD,
  [
    'label' => __('New password'),
    'type' => 'password',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  ChangePasswordViewModel::CONFIRM_PASSWORD,
  [
    'label' => __('Confirm password'),
    'type' => 'password',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Update'), ['name' => ApplicationControllerBase::SUBMIT_CHANGE_PASSWORD]
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
