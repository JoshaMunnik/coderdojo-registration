<?php

use App\Controller\AdministratorController;
use App\Controller\UsersController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\Value\Language;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var UserEntity[] $users
 */

const REMOVE_DIALOG = 'remove';

?>
<?= $this->Styling->title(__('Users')) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(__('Add user'), [UsersController::EDIT]) ?>
<?= $this->Styling->linkButton(
  __('Home'), AdministratorController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php
echo $this->Styling->beginSortedTable(HtmlStorageKey::USERS_TABLE, true);
echo $this->Styling->sortedTableHeader([
  __('Email') => CellDataTypeEnum::TEXT,
  __('Name') => CellDataTypeEnum::TEXT,
  __('Language') => CellDataTypeEnum::TEXT,
  __('Created') => CellDataTypeEnum::DATE,
  __('Participants') => CellDataTypeEnum::NUMBER,
  null,
]);
foreach ($users as $user) {
  echo $this->Styling->sortedTableRow(
    [
      $user->email,
      $user->name,
      Language::getName($user->language_id),
      $user->created,
      [count($user->participants) => ContentPositionEnum::END],
    ],
    [
      $this->Styling->tableLinkIconButton(
        ButtonIconEnum::EDIT,
        [UsersController::EDIT, $user->id]
      ),
      $this->Styling->tableIconButton(
        ButtonIconEnum::REMOVE,
        ButtonColorEnum::DANGER,
        [
          HtmlAction::SHOW_DIALOG => '#'.REMOVE_DIALOG,
          HtmlData::USER_ID => $user->id,
          HtmlData::USER_NAME => $user->name,
        ]
      ),

    ],
    $this->isUser($user)
  );
}
echo $this->Styling->endSortedTable();
?>
<?= $this->element(
  'dialog/remove_user', ['id' => REMOVE_DIALOG, 'data' => new IdViewModel()]
) ?>
