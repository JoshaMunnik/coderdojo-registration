<?php

use App\Controller\ParticipantsController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\Participants\RemoveParticipantViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var RemoveParticipantViewModel $data
 * @var string $id
 */

$name = '<span '.HtmlData::PARTICIPANT_NAME.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  ParticipantsController::REMOVE,
  [
    RemoveParticipantViewModel::ID => HtmlData::PARTICIPANT_ID,
    RemoveParticipantViewModel::IS_CHECKIN,
    RemoveParticipantViewModel::EVENT_ID,
  ]
) ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove {0}?', $name)
) ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>

