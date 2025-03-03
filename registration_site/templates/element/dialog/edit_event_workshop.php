<?php

use App\Controller\EventWorkshopsController;
use App\Model\Constant\HtmlData;
use App\Model\View\EventWorkshops\EditEventWorkshopViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditEventWorkshopViewModel $data
 * @var string $id
 * @var string $eventId
 */

$workshopName = '<span '.HtmlData::WORKSHOP_NAME.'>'
  .($data->workshop?->getName() ?? '')
  .'</span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Update workshop'),
  $data,
  [EventWorkshopsController::INDEX, $eventId],
  [
    EditEventWorkshopViewModel::ID => HtmlData::EVENT_WORKSHOP_ID,
    EditEventWorkshopViewModel::WORKSHOP_ID => HtmlData::WORKSHOP_ID,
  ]
) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Styling->textBlock(__('Workshop: {0}', $workshopName)) ?>
<?= $this->Form->control(
  EditEventWorkshopViewModel::PLACE_COUNT,
  [
    'label' => __('Max places'),
    'type' => 'number',
    'max' => 1000,
    'min' => 1,
    'required' => true,
    HtmlData::PLACE_COUNT,
    'id' => false,
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Update'),
  [
    'name' => EventWorkshopsController::SUBMIT_EDIT,
  ]
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
