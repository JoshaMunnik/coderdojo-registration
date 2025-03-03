<?php

use App\Controller\EventWorkshopsController;
use App\Model\Constant\HtmlData;
use App\Model\Entity\WorkshopEntity;
use App\Model\View\EventWorkshops\EditEventWorkshopViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EditEventWorkshopViewModel $data
 * @var WorkshopEntity[] $workshops
 * @var string $id
 * @var string $eventId
 */

$workshopOptions = [];
foreach ($workshops as $workshop) {
  $workshopOptions[$workshop->id] = $workshop->getName();
}

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Add workshop'),
  $data,
  [EventWorkshopsController::INDEX, $eventId],
  [EditEventWorkshopViewModel::ID]
) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditEventWorkshopViewModel::WORKSHOP_ID,
  [
    'label' => __('Workshop'),
    'type' => 'select',
    'options' => $workshopOptions,
    'required' => true,
    'empty' => __('Select workshop'),
    'id' => false,
  ],
) ?>
<?= $this->Form->control(
  EditEventWorkshopViewModel::PLACE_COUNT,
  [
    'label' => __('Max places'),
    'type' => 'number',
    'max' => 1000,
    'min' => 1,
    'required' => true,
    'id' => false,
    HtmlData::PLACE_COUNT
  ],
) ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Form->submit(
  __('Add'),
  [
    'name' => EventWorkshopsController::SUBMIT_ADD,
  ]
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
