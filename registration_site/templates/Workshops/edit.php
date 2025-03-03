<?php

use App\Controller\WorkshopsController;
use App\Model\Enum\ButtonColorEnum;
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
<?= $this->Styling->beginFormContainer() ?>
<?php foreach (Language::getList() as $id => $languageName): ?>
  <?= $this->Styling->smallTitle($languageName) ?>
  <?= $this->Form->control($data->nameField($id), ['label' => __('Name')]) ?>
  <?= $this->Form->control(
    $data->descriptionField($id),
    [
      'label' => __('Description'),
      'type' => 'textarea',
      'rows' => 5
    ]
  ) ?>
<?php endforeach; ?>
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
<?= $this->Styling->endFormContainer() ?>
<?= $this->Form->hidden(EditWorkshopViewModel::ID) ?>
<?= $this->Form->end() ?>
