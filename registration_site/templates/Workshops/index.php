<?php

use App\Controller\AdministratorController;
use App\Controller\WorkshopsController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\HtmlStorageKey;
use App\Model\Entity\WorkshopEntity;
use App\Model\Entity\WorkshopTextEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\View\IdViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var WorkshopEntity[] $workshops
 */


?>
<?= $this->Styling->title(__('Workshops')) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(__('Add workshop'), [WorkshopsController::EDIT]) ?>
<?= $this->Styling->linkButton(
  __('Home'), AdministratorController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?php if (empty($workshops)) {
  $this->Styling->smallTitle(__('No workshops'));
}
else {
  echo $this->Styling->beginSortedTable(HtmlStorageKey::WORKSHOPS_TABLE);
  echo $this->Styling->sortedTableHeader([
    __('Name') => CellDataTypeEnum::TEXT,
    null,
  ]);
  foreach ($workshops as $workshop) {
    echo $this->Styling->sortedTableRow(
      [
        $workshop->getText(WorkshopTextEntity::NAME)
      ],
      [
        $this->Styling->tableLinkIconButton(
          ButtonIconEnum::EDIT,
          [WorkshopsController::EDIT, $workshop->id]
        ),
        $this->Styling->tableIconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#remove',
            HtmlData::WORKSHOP_ID => $workshop->id,
            HtmlData::WORKSHOP_NAME => $workshop->getName(),
          ]
        ),
      ]
    );
  }
  echo $this->element(
    'dialog/remove_workshop', ['id' => 'remove', 'data' => new IdViewModel()]
  );
} ?>
