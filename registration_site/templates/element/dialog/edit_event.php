<?php

use App\Controller\EventsController;
use App\Model\Constant\HtmlData;
use App\Model\View\Events\EditEventViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditEventViewModel $data
 * @var string $id
 */

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Update event'),
  $data,
  null,
  [EditEventViewModel::ID => HtmlData::EVENT_ID])
?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditEventViewModel::EVENT_DATE,
  [
    'label' => __('Event date'),
    'type' => 'datetime-local',
    'required' => true,
    HtmlData::EVENT_DATE,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  EditEventViewModel::SIGNUP_DATE,
  [
    'label' => __('Signup date'),
    'type' => 'datetime-local',
    'required' => true,
    HtmlData::SIGNUP_DATE,
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  EditEventViewModel::PARTICIPANT_TYPE,
  [
    'label' => __('Target audience'),
    'type' => 'select',
    'options' => $data->participant_types,
    'empty' => __('Choose an audience type'),
    'required' => true,
    HtmlData::PARTICIPANT_TYPE,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  'Update',
  [
    'name' => EventsController::SUBMIT_EDIT,
  ],
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
