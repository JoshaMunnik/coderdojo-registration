<?php

use App\Controller\UsersController;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Value\Language;
use App\Model\View\Users\EditUserViewModel;
use App\View\ApplicationView;

/**
 * @var EditUserViewModel $data
 * @var ApplicationView $this
 */

?>
<?= $this->Styling->title($data->isNew() ? __('Create user') : __('Edit user')) ?>
<?= $this->element('messages') ?>
<?= $this->createForm($data) ?>
<?= $this->Styling->beginFormContainer() ?>
<?= $this->Form->control(
  EditUserViewModel::EMAIL,
  [
    'label' => __('Email'),
    'type' => 'email',
    'required' => true
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::NAME,
  [
    'label' => __('Name'),
    'type' => 'text',
    'required' => true
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::PASSWORD,
  [
    'label' => $data->isNew()
      ? __('Password')
      : __('Password (leave empty to keep current password)'),
    'type' => 'password',
    'required' => $data->isNew()
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::PHONE,
  [
    'label' => __('Phone number'),
    'type' => 'text',
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::ADMINISTRATOR,
  [
    'type' => 'checkbox',
    'label' => __('User is an administrator')
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::LANGUAGE,
  [
    'label' => __('Language'),
    'options' => Language::getList(),
    'type' => 'select',
    'required' => true
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::MAILING_LIST,
  [
    'type' => 'checkbox',
    'label' => __('User is on the mailing list')
  ]
) ?>
<?= $this->Form->control(
  EditUserViewModel::DISABLE_EMAIL,
  [
    'type' => 'checkbox',
    'label' => __('Do not send emails to this user (user is a test user)')
  ]
) ?>
<?= $this->Styling->beginFormButtons() ?>
<?= $this->Form->button(__('Save')) ?>
<?= $this->Styling->linkButton(
  __('Cancel'), UsersController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endFormButtons() ?>
<?= $this->Styling->endFormContainer() ?>
<?= $this->Form->hidden(EditUserViewModel::ID) ?>
<?= $this->Form->end() ?>
