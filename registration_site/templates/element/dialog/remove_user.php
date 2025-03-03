<?php

use App\Controller\UsersController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var IdViewModel $data
 * @var string $id
 */

$name = '<span '.HtmlData::USER_NAME.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  [UsersController::REMOVE],
  [IdViewModel::ID => HtmlData::USER_ID]
) ?>
<?= $this->Styling->beginColumn() ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove the user {0}?', $name)
) ?>
<?= $this->Styling->textBlock(
  __('Related participants for pending events will removed, other related participants will be anonymized.')
) ?>
<?= $this->Styling->endColumn() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
