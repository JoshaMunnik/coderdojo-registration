<?php

use App\Controller\UsersController;
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

$date = '<span '.HtmlData::EVENT_DATE.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  [UsersController::REMOVE_ABSENT_USER],
  [IdViewModel::ID => HtmlData::ABSENT_USER_ID]
) ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove the absent entry for the event at {0}?', $date)
) ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
