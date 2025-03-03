<?php

use App\Controller\EventsController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var IdViewModel $data
 * @var string $id
 */

$date = '<span '.HtmlData::EVENT_DATE.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  [EventsController::REMOVE],
  [IdViewModel::ID => HtmlData::EVENT_ID]
) ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove the event for {0}?', $date)
) ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
