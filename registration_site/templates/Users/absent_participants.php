<?php

use App\Controller\UsersController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\AbsentParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var AbsentParticipantEntity[] $absentParticipants
 * @var UserEntity $user;
 */

const REMOVE_DIALOG = 'remove';
?>
<?= $this->Styling->title(__('Absent participants for {0}', $user->name)) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Back to users'), UsersController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($absentParticipants)) {
  echo $this->Styling->smallTitle(__('No absent participants'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::ABSENT_PARTICIPANTS_TABLE);
  echo $this->Styling->sortedTableHeader([
    __('Event') => CellDataTypeEnum::DATE,
    null,
  ]);
  foreach ($absentParticipants as $absentParticipant) {
    echo $this->Styling->sortedTableRow(
      [
        $absentParticipant->event->getEventDateAsText(),
      ],
      [
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE_DIALOG,
            HtmlData::ABSENT_PARTICIPANT_ID => $absentParticipant->id,
            HtmlData::EVENT_DATE => $absentParticipant->event->getEventDateAsText(),
          ]
        ),
      ]
    );
  }
  echo $this->element(
    'dialog/remove_absent_participant',
    [
      'id' => REMOVE_DIALOG,
      'data' => new IdViewModel(),
    ]
  );
} ?>
