<?php

use App\Controller\UserController;
use App\Model\Constant\HtmlData;
use App\Model\View\Events\EditEventViewModel;
use App\Model\View\User\EditParticipantViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditEventViewModel $data
 * @var string $id
 */

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Update participant'),
  $data,
  null,
  [EditParticipantViewModel::ID => HtmlData::PARTICIPANT_ID]
) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditParticipantViewModel::NAME,
  [
    'label' => __('Name'),
    'type' => 'text',
    'required' => true,
    HtmlData::PARTICIPANT_NAME,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  EditParticipantViewModel::HAS_LAPTOP,
  [
    'label' => __('Has own laptop'),
    'type' => 'checkbox',
    HtmlData::PARTICIPANT_HAS_LAPTOP,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  'Update',
  [
    'name' => UserController::SUBMIT_EDIT_PARTICIPANT,
  ],
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
