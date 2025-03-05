<?php

use App\Controller\EventsController;
use App\Controller\EventWorkshopsController;
use App\Model\Constant\ClickAction;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\WorkshopEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\Tables;
use App\Model\View\EventWorkshops\EditEventWorkshopViewModel;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var EventEntity $event
 * @var EventWorkshopEntity[] $eventWorkshops
 * @var WorkshopEntity[] $workshops
 * @var EditEventWorkshopViewModel $addEventWorkshopData
 * @var EditEventWorkshopViewModel $editEventWorkshopData
 */

const EDIT = 'edit';
const ADD = 'add';
const REMOVE = 'remove';

?>
<?= $this->Styling->title(__('Workshops for event for {0}', $event->getEventDateAsText())) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->button(
  __('Add workshop'),
  ButtonColorEnum::PRIMARY,
  [
    HtmlAction::SHOW_DIALOG => '#'.ADD,
    HtmlData::PLACE_COUNT => '1',
  ]) ?>
<?= $this->Styling->linkButton(
  __('Download'),
  [EventWorkshopsController::DOWNLOAD, $event->id],
) ?>
<?= $this->Styling->linkButton(
  __('Back to events'),
  [EventsController::INDEX, $event->id],
  ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($eventWorkshops)) {
  echo $this->Styling->smallTitle(__('No workshops'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::EVENT_WORKSHOPS_TABLE);
  echo $this->Styling->sortedTableHeader([
    __('Workshop') => CellDataTypeEnum::TEXT,
    __('Places') => CellDataTypeEnum::NUMBER,
    __('Participants') => CellDataTypeEnum::NUMBER,
    __('Waiting') => CellDataTypeEnum::NUMBER,
    __('Laptops needed') => CellDataTypeEnum::NUMBER,
    null,
  ]);
  foreach ($eventWorkshops as $eventWorkshop) {
    $copyAttribute = $event->hasActiveSignup()
      ? [
        HtmlAction::CLICK_ACTION => 'set-attribute',
        ClickAction::ATTRIBUTE => 'min',
        ClickAction::TARGET => '['.HtmlData::PLACE_COUNT.']',
      ]
      : [];
    $participantCount = Tables::participants()->getCountForWorkshop($eventWorkshop);
    echo $this->Styling->sortedTableRow([
      $eventWorkshop->getName(),
      [$eventWorkshop->place_count => ContentPositionEnum::END],
      [min($participantCount, $eventWorkshop->place_count) => ContentPositionEnum::END],
      [max(0, $participantCount - $eventWorkshop->place_count) => ContentPositionEnum::END],
      [$eventWorkshop->getLaptopsNeededCount() => ContentPositionEnum::END],
    ],
      [
        $this->Styling->tableIconButton(
          ButtonIconEnum::EDIT,
          ButtonColorEnum::PRIMARY,
          [
            HtmlAction::SHOW_DIALOG => '#'.EDIT,
            HtmlData::EVENT_WORKSHOP_ID => $eventWorkshop->id,
            HtmlData::WORKSHOP_NAME => $eventWorkshop->getName(),
            HtmlData::WORKSHOP_ID => $eventWorkshop->workshop_id,
            HtmlData::PLACE_COUNT => $eventWorkshop->place_count,
            // if copyAttribute is empty, this data attribute is ignored; so it is safe to add
            ClickAction::DATA => $eventWorkshop->place_count,
            ...$copyAttribute,
          ]
        ),
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE,
            HtmlData::EVENT_WORKSHOP_ID => $eventWorkshop->id,
            HtmlData::WORKSHOP_NAME => $eventWorkshop->getName(),
          ]
        )
      ]);
  }
  echo $this->Styling->endSortedTable();
  echo $this->element(
    'dialog/remove_event_workshop', ['id' => REMOVE, 'data' => new IdViewModel()]
  );
  echo $this->element(
    'dialog/edit_event_workshop',
    [
      'id' => EDIT,
      'data' => $editEventWorkshopData,
      'eventId' => $event->id,
    ]
  );
} ?>
<?= $this->element(
  'dialog/add_event_workshop',
  [
    'id' => ADD,
    'data' => $addEventWorkshopData,
    'eventId' => $event->id,
  ]
) ?>
