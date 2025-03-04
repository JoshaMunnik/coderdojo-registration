<?php

use App\Controller\WorkshopsController;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\GapEnum;
use App\Model\Value\Language;
use App\Model\View\Workshops\EditWorkshopViewModel;
use App\View\ApplicationView;

/**
 * @var EditWorkshopViewModel $data
 * @var ApplicationView $this
 */

$this->Html->script(['tinymce/tinymce.min.js', 'workshops-edit'], ['block' => true]);

?>
<?= $this->Styling->title($data->isNew() ? __('Create workshop') : __('Edit workshop')) ?>
<?= $this->element('messages') ?>
<?= $this->createForm($data) ?>
<?= $this->Styling->beginColumn(GapEnum::FORM) ?>
<?= $this->Styling->beginTabsContainer() ?>
<?php
$selected = true;
foreach (Language::getList() as $id => $languageName):
  ?>
  <?= $this->Styling->beginTab($languageName, $selected) ?>
  <?= $this->Styling->beginFormContainer() ?>
  <?= $this->Form->control($data->nameField($id), ['label' => __('Name')]) ?>
  <?=
  $this->Form->control(
    $data->descriptionField($id),
    [
      'label' => __('Description'),
      'type' => 'textarea',
      'rows' => 7
    ],
  )
  ?>
  <?= $this->Styling->endFormContainer() ?>
  <?= $this->Styling->endTab() ?>
  <?php
  $selected = false;
endforeach;
?>
<?= $this->Styling->endTabsContainer() ?>
<?= $this->Form->control(
  EditWorkshopViewModel::LAPTOP,
  [
    'type' => 'checkbox',
    'label' => __('User can use own laptop')
  ]
) ?>
<?= $this->Styling->beginFormButtons() ?>
<?= $this->Form->button(__('Save')) ?>
<?= $this->Styling->linkButton(
  __('Cancel'), WorkshopsController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endFormButtons() ?>
<?= $this->Styling->endColumn() ?>
<?= $this->Form->hidden(EditWorkshopViewModel::ID) ?>
<?= $this->Form->end() ?>
