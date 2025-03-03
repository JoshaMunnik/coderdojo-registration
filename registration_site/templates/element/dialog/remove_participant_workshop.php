<?php

use App\Controller\UserController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\IdViewModel;
use App\Model\View\User\RemoveWorkshopViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var IdViewModel $data
 * @var string $id
 */

$workshopName = '<span '.HtmlData::WORKSHOP_NAME.'></span>';
$participantName = '<span '.HtmlData::PARTICIPANT_NAME.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Confirm remove'),
  $data,
  [UserController::REMOVE_WORKSHOP],
  [
    RemoveWorkshopViewModel::PARTICIPANT_ID => HtmlData::PARTICIPANT_ID,
    RemoveWorkshopViewModel::INDEX => HtmlData::WORKSHOP_INDEX,
  ]
) ?>
<?= $this->Styling->textBlock(
  __('Are you sure you want to remove {0} from the workshop {1}?', $participantName, $workshopName)
) ?>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(__('Yes, remove'), ButtonColorEnum::DANGER) ?>
<?= $this->Styling->closeButton(__('No, cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
