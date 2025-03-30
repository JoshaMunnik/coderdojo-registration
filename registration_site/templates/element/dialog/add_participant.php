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
  $id, __('Add participant'), $data, null, [EditEventViewModel::ID]
) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditParticipantViewModel::NAME,
  [
    'label' => __('Name'),
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
<?= $this->Form->control(
  EditParticipantViewModel::CAN_LEAVE,
  [
    'label' => __('Can leave the event by themselves'),
    'type' => 'checkbox',
    HtmlData::PARTICIPANT_CAN_LEAVE,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Add'),
  [
    'name' => UserController::SUBMIT_ADD_PARTICIPANT,
  ],
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
