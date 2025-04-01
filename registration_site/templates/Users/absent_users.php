<?php

use App\Controller\UsersController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\AbsentUserEntity;
use App\Model\Entity\AbsentUserWithEventEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var AbsentUserWithEventEntity[] $absentUsers
 * @var UserEntity $user;
 */

const REMOVE_DIALOG = 'remove';
?>
<?= $this->Styling->title(__('Absent at events for {0}', $user->name)) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Back to users'), UsersController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($absentUsers)) {
  echo $this->Styling->smallTitle(__('No absent events'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::ABSENT_PARTICIPANTS_TABLE);
  echo $this->Styling->sortedTableHeader([
    [__('Event'), CellDataTypeEnum::DATE],
    null,
  ]);
  foreach ($absentUsers as $absentUser) {
    echo $this->Styling->sortedTableRow(
      [
        $absentUser->event->getEventDateAsText(),
      ],
      [
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE_DIALOG,
            HtmlData::ABSENT_USER_ID => $absentUser->id,
            HtmlData::EVENT_DATE => $absentUser->event->getEventDateAsText(),
          ]
        ),
      ]
    );
  }
  echo $this->element(
    'dialog/remove_absent_user',
    [
      'id' => REMOVE_DIALOG,
      'data' => new IdViewModel(),
    ]
  );
} ?>
