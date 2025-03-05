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
  __('Confirm anonymize'),
  $data,
  [EventsController::ANONYMIZE],
  [IdViewModel::ID => HtmlData::EVENT_ID]
) ?>
<?= $this->Styling->beginColumn() ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to anonymize the participants the event for {0}?', $date)
) ?>
<?= $this->Styling->textList(
  __('Anonymizing the participants will perform the following actions:'),
  [
    __('Check and add absent information.'),
    __('Clear name of participant.'),
    __('Remove reference to user from the participant.')
  ]
) ?>
<?= $this->Styling->strongTextBlock(__('This action can not be undone!')) ?>
<?= $this->Styling->endColumn() ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, anonymize'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
