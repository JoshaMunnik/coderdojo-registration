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
use App\Model\Enum\CellStylingEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\Value\Language;
use App\Model\View\Participants\RemoveParticipantViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ParticipantEntity[] $participants
 * @var EventEntity $event
 * @var EventWorkshopEntity[] $eventWorkshops
 */

const REMOVE_DIALOG = 'remove';
const TABLE = 'participants-table';

$this->Html->scriptBlock(
  'import {participantsManageCheckins} from "./js/participants-manage-checkins.js";'.
  'participantsManageCheckins.init("'
  .$this->Url->build($this->url([ParticipantsController::CHECKIN]))
  .'", "'
  .$this->getRequest()->getAttribute('csrfToken')
  .'");',
  [
    'block' => 'scriptBottom',
    'type' => 'module',
  ]
);

?>
<?= $this->Styling->title(__('Checkin manager for {0}', $event->getEventDateAsText())) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginRow() ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Participants'), [ParticipantsController::INDEX, $event->id]
) ?>
<?= $this->Styling->linkButton(
  __('Back to events'), EventsController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<div class="cd-layout__spacer"></div>
<?= $this->Form->text(
  'filter',
  [
    'placeholder' => __('filter'),
    'data-uf-filter-table' => '#'.TABLE,
    'class' => 'cd-form__filter',
  ]
) ?>
<?= $this->Styling->endRow() ?>
<?php if (empty($participants)) {
  echo $this->Styling->smallTitle(__('No participants'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::CHECKIN_TABLE, true);
  echo $this->Styling->sortedTableHeader([
    [__('Participant'), CellDataTypeEnum::TEXT],
    [__('Workshop'), CellDataTypeEnum::TEXT],
    [__('Leave'), CellDataTypeEnum::TEXT],
    [__('Language'), CellDataTypeEnum::TEXT],
    [__('User'), CellDataTypeEnum::TEXT],
    [__('Code'), CellDataTypeEnum::TEXT],
    [__('Checkin'), CellDataTypeEnum::NUMBER, CellStylingEnum::TIGHT],
    null,
  ]);
  foreach ($participants as $participant) {
    $eventWorkshopId = $participant->event_workshop_1_id ?? $participant->event_workshop_2_id;
    $eventWorkshop = $eventWorkshops[$eventWorkshopId];
    $workshop = $this->element('workshop_cell', [
      'participant' => $participant,
      'eventWorkshop' => $eventWorkshop,
      'buttons' => false,
    ]);
    $toggleButton = $this->Styling->toggleButton(
      $participant->checkin_date !== null,
      __('yes'),
      __('no'),
      [
        HtmlData::PARTICIPANT_ID => $participant->id,
        HtmlData::CHECKIN_BUTTON,
      ]
    );
    $leave = $participant->can_leave ? __('Yes') : __('No');
    echo $this->Styling->sortedTableRow(
      [
        $participant->name,
        [
          $workshop,
          ContentPositionEnum::CENTER,
          'data-uf-sort-value' => $eventWorkshop->getName(),
        ],
        [
          $leave,
          ContentPositionEnum::CENTER,
        ],
        $participant->user ? Language::getName($participant->user->language_id) : '',
        $participant->user?->name ?? '',
        $participant->user?->public_id ?? '',
        [
          $toggleButton,
          ContentPositionEnum::END,
          'data-uf-sort-value' => $participant->checkin_date ? 1 : 0,
          'data-uf-sort-no-caching' => '',
        ],
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
        )
      ],
    );
  }
  echo $this->element(
    'dialog/remove_participant_from_event',
    [
      'id' => REMOVE_DIALOG,
      'data' => new RemoveParticipantViewModel(true, $event->id)
    ]
  );
} ?>
