<?php

use App\Controller\EventsController;
use App\Controller\ParticipantsController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\View\Participants\RemoveParticipantViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ParticipantEntity[] $participants
 * @var EventEntity $event
 * @var EventWorkshopEntity[] $eventWorkshops
 */

const REMOVE_DIALOG = 'remove';
foreach ($eventWorkshops as $eventWorkshop) {
  $eventWorkshops[$eventWorkshop->id] = $eventWorkshop;
}
?>
<?= $this->Styling->title(__('Participants for {0}', $event->getEventDateAsText())) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Checkins'), [ParticipantsController::MANAGE_CHECKIN, $event->id]
) ?>
<?= $this->Styling->linkButton(
  __('Download'), [ParticipantsController::DOWNLOAD, $event->id]
) ?>
<?= $this->Styling->linkButton(
  __('Back to events'), EventsController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($participants)) {
  echo $this->Styling->smallTitle(__('No participants'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::PARTICIPANTS_TABLE);
  echo $this->Styling->sortedTableHeader([
    __('Participant name') => CellDataTypeEnum::TEXT,
    __('User email') => CellDataTypeEnum::TEXT,
    __('User name') => CellDataTypeEnum::TEXT,
    __('Workshop') => CellDataTypeEnum::TEXT,
    __('Backup workshop') => CellDataTypeEnum::TEXT,
    __('Laptop') => CellDataTypeEnum::TEXT,
    __('Participated') => CellDataTypeEnum::TEXT,
    null,
  ]);
  foreach ($participants as $participant) {
    $workshop1 = $participant->event_workshop_1_id == null
      ? '-'
      : $this->element(
        'workshop_cell',
        [
          'participant' => $participant,
          'eventWorkshop' => $eventWorkshops[$participant->event_workshop_1_id],
          'buttons' => false,
        ]
      );
    $workshop2 = $participant->event_workshop_2_id == null
      ? '-'
      : $this->element(
        'workshop_cell',
        [
          'participant' => $participant,
          'eventWorkshop' => $eventWorkshops[$participant->event_workshop_2_id],
          'buttons' => false,
        ]
      );
    $finished = $event->isFinished()
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
        $participant->user?->email ?? '-',
        $participant->user?->name ?? '-',
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
    'dialog/remove_participant_from_event',
    [
      'id' => REMOVE_DIALOG,
      'data' => new RemoveParticipantViewModel(false, $event->id)
    ]
  );
} ?>
