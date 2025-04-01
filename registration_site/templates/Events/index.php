<?php

use App\Controller\AdministratorController;
use App\Controller\EventWorkshopsController;
use App\Controller\ParticipantsController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Data\EventWithCountsData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\CellStylingEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\Value\ParticipantType;
use App\Model\View\Events\EditEventViewModel;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;
use Cake\Core\Configure;

/**
 * @var ApplicationView $this
 * @var EventWithCountsData[] $events
 * @var EditEventViewModel $editEventData
 * @var EditEventViewModel $addEventData
 */

const EDIT = 'edit';
const ADD = 'add';
const REMOVE = 'remove';
const ANONYMIZE = 'anonymize';

?>
<?= $this->Styling->title(__('{0} Events', Configure::read('Custom.eventName'))) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->button(
  __('Add event'),
  ButtonColorEnum::PRIMARY,
  [
    HtmlAction::SHOW_DIALOG => '#'.ADD,
    HtmlData::EVENT_DATE => '',
    HtmlData::SIGNUP_DATE => '',
    HtmlData::PARTICIPANT_TYPE => '',
  ],
  true
) ?>
<?= $this->Styling->linkButton(
  __('Home'), AdministratorController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($events)) {
  echo $this->Styling->smallTitle(__('No events'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::EVENTS_TABLE);
  echo $this->Styling->sortedTableHeader([
    [__('Event date'), CellDataTypeEnum::DATE],
    [__('Signup date'), CellDataTypeEnum::DATE, CellStylingEnum::HIDE_ON_MOBILE],
    [__('Audience'), CellDataTypeEnum::NUMBER, CellStylingEnum::HIDE_ON_MOBILE],
    [__('Participants'), CellDataTypeEnum::NUMBER, CellStylingEnum::HIDE_ON_MOBILE],
    [__('Workshops'), CellDataTypeEnum::NUMBER, CellStylingEnum::HIDE_ON_MOBILE],
    [__('Places'), CellDataTypeEnum::NUMBER, CellStylingEnum::HIDE_ON_MOBILE],
    null,
  ]);
  foreach ($events as $eventInfo) {
    echo $this->Styling->sortedTableRow(
      [
        $eventInfo->event->event_date,
        [
          $eventInfo->event->signup_date,
          CellStylingEnum::HIDE_ON_MOBILE
        ],
        [
          ParticipantType::getName($eventInfo->event->participant_type),
          ContentPositionEnum::CENTER,
          CellStylingEnum::HIDE_ON_MOBILE
        ],
        [
          $eventInfo->participatingCount.' / '.$eventInfo->waitingCount,
          ContentPositionEnum::END,
          CellStylingEnum::HIDE_ON_MOBILE
        ],
        [
          $eventInfo->workshopsCount,
          ContentPositionEnum::END,
          CellStylingEnum::HIDE_ON_MOBILE
        ],
        [
          $eventInfo->placesCount,
          ContentPositionEnum::END,
          CellStylingEnum::HIDE_ON_MOBILE
        ],
      ],
      [
        $this->Styling->tableIconButton(
          ButtonIconEnum::EDIT,
          ButtonColorEnum::PRIMARY,
          [
            HtmlAction::SHOW_DIALOG => '#'.EDIT,
            HtmlData::EVENT_ID => $eventInfo->event->id,
            HtmlData::EVENT_DATE => $eventInfo->event->event_date->format('Y-m-d H:i'),
            HtmlData::SIGNUP_DATE => $eventInfo->event->signup_date->format('Y-m-d H:i'),
            HtmlData::PARTICIPANT_TYPE => $eventInfo->event->participant_type,
          ],
          true,
        ),
        $this->Styling->tableLinkButton(
          __('Workshops'),
          [EventWorkshopsController::INDEX, $eventInfo->event->id],
          ButtonColorEnum::PRIMARY,
          true,
        ),
        $this->Styling->tableLinkIconButton(
          ButtonIconEnum::PARTICIPANTS,
          [ParticipantsController::INDEX, $eventInfo->event->id],
          ButtonColorEnum::PRIMARY,
          true,
        ),
        $eventInfo->event->hasActiveSignup()
          ? $this->Styling->tableLinkIconButton(
          ButtonIconEnum::QR_CODE,
          [ParticipantsController::SCAN, $eventInfo->event->id],
        )
          : $this->Styling->tableStaticIconButton(ButtonIconEnum::QR_CODE),
        $eventInfo->event->isFinished()
          ? $this->Styling->tableButton(
          __('Anonymize'),
          ButtonColorEnum::WARNING,
          [
            HtmlAction::SHOW_DIALOG => '#'.ANONYMIZE,
            HtmlData::EVENT_ID => $eventInfo->event->id,
            HtmlData::EVENT_DATE => $eventInfo->event->event_date->format('Y-m-d H:i'),
          ],
          true
        )
          : $this->Styling->tableStaticButton(__('Anonymize'), ButtonColorEnum::DISABLED, [], true),
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE,
            HtmlData::EVENT_ID => $eventInfo->event->id,
            HtmlData::EVENT_DATE => $eventInfo->event->event_date->format('Y-m-d H:i'),
          ],
          true
        ),
      ],
      $eventInfo->event->hasActiveSignup()
    );
  }
  echo $this->Styling->endSortedTable();
  echo $this->element(
    'dialog/remove_event', ['id' => REMOVE, 'data' => new IdViewModel()]
  );
  echo $this->element(
    'dialog/edit_event', ['id' => EDIT, 'data' => $editEventData]
  );
  echo $this->element(
    'dialog/anonymize_event', ['id' => ANONYMIZE, 'data' => new IdViewModel()]
  );
}
?>
<?= $this->element(
  'dialog/add_event', ['id' => ADD, 'data' => $addEventData]
) ?>
