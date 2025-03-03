<?php

use App\Lib\Controller\ApplicationControllerBase;
use App\Model\View\EditProfileViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditProfileViewModel $data
 * @var string $id
 */

?>
<?=
$this->Styling->beginFormDialog($id, __('Edit your profile'), $data) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditProfileViewModel::NAME,
  [
    'label' => __('Name'),
    'type' => 'text',
    'required' => true,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  EditProfileViewModel::PHONE,
  [
    'label' => __('Phone number (optional)'),
    'type' => 'text',
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Update'), ['name' => ApplicationControllerBase::SUBMIT_EDIT_PROFILE]
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
