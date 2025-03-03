<?php

use App\Controller\WorkshopsController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var IdViewModel $data
 * @var string $id
 */

$name = '<span '.HtmlData::WORKSHOP_NAME.'></span>';


?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  [WorkshopsController::REMOVE],
  [IdViewModel::ID => HtmlData::WORKSHOP_ID]
) ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove the workshop {0}?', $name)
) ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
