<?php

use App\Controller\UsersController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\ParticipantWithEventAndWorkshopsEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ParticipantWithEventAndWorkshopsEntity[] $participants
 * @var UserEntity $user;
 */

const REMOVE_DIALOG = 'remove';
?>
<?= $this->Styling->title(__('Participants for {0}', $user->name)) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Back to users'), UsersController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($participants)) {
  echo $this->Styling->smallTitle(__('No participants'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::USER_PARTICIPANTS_TABLE, true);
  echo $this->Styling->sortedTableHeader([
    __('Participant name') => CellDataTypeEnum::TEXT,
    __('Event') => CellDataTypeEnum::DATE,
    __('Workshop') => CellDataTypeEnum::TEXT,
    __('Backup workshop') => CellDataTypeEnum::TEXT,
    __('Laptop') => CellDataTypeEnum::TEXT,
    __('Participated') => CellDataTypeEnum::TEXT,
    null,
  ]);
  foreach ($participants as $participant) {
    $eventWorkshops = [];
    if ($participant->event_workshop_1_id != null) {
      $eventWorkshops[$participant->event_workshop_1_id] = $participant->workshop_1;
    }
    if ($participant->event_workshop_2_id != null) {
      $eventWorkshops[$participant->event_workshop_2_id] = $participant->workshop_2;
    }
    $workshop1 = $participant->event_workshop_1_id == null
      ? '-'
      : $this->element(
        'workshop_cell',
        [
          'participant' => $participant,
          'eventWorkshop' => $participant->workshop_1,
          'buttons' => false,
        ]
      );
    $workshop2 = $participant->event_workshop_2_id == null
      ? '-'
      : $this->element(
        'workshop_cell',
        [
          'participant' => $participant,
          'eventWorkshop' => $participant->workshop_2,
          'buttons' => false,
        ]
      );
    $finished = $participant->event->isFinished()
      ?
      (
      $participant->checkin_date
        ? $this->Styling->successText(__('yes'))
        :
        (
        $participant->isParticipating($eventWorkshops)
          ? $this->Styling->dangerText(__('no'))
          : ''
        )
      )
      : '';
    echo $this->Styling->sortedTableRow(
      [
        $participant->name,
        $participant->event->event_date,
        [$workshop1 => ContentPositionEnum::CENTER],
        [$workshop2 => ContentPositionEnum::CENTER],
        [$this->Styling->checkedCheckbox($participant->has_laptop) => ContentPositionEnum::CENTER],
        [$finished => ContentPositionEnum::CENTER],
      ],
      [
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE_DIALOG,
            HtmlData::PARTICIPANT_ID => $participant->id,
            HtmlData::PARTICIPANT_NAME => $participant->name,
          ]
        ),
      ]
    );
  }
  echo $this->element(
    'dialog/remove_participant_from_user',
    [
      'id' => REMOVE_DIALOG,
      'data' => new IdViewModel(),
      'postUrl' => [UsersController::REMOVE_PARTICIPANT],
    ]
  );
} ?>
