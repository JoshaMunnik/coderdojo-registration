<?php

use App\Controller\AccountController;
use App\Controller\AdministratorController;
use App\Controller\UserController;
use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\WorkshopIndex;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Value\ParticipantType;
use App\Model\View\ChangePasswordViewModel;
use App\Model\View\EditProfileViewModel;
use App\Model\View\IdViewModel;
use App\Model\View\User\EditParticipantViewModel;
use App\Model\View\User\RemoveWorkshopViewModel;
use App\Model\View\User\SelectWorkshopViewModel;
use App\View\ApplicationView;
use Cake\Core\Configure;

/**
 * @var ApplicationView $this
 * @var EditProfileViewModel $editProfileData
 * @var ChangePasswordViewModel $changePasswordData
 * @var EventEntity|null $event
 * @var ParticipantEntity[] $participants
 * @var EventWorkshopEntity[] $eventWorkshops
 * @var EditParticipantViewModel $addParticipantData
 * @var EditParticipantViewModel $editParticipantData
 */

const EDIT_PROFILE = 'edit-profile';
const CHANGE_PASSWORD = 'change-password';
const ADD_PARTICIPANT = 'add-participant';
const EDIT_PARTICIPANT = 'edit-participant';
const REMOVE_PARTICIPANT = 'remove-participant';
const ADD_WORKSHOP = 'add-workshop';
const REMOVE_WORKSHOP = 'remove-workshop';

$this->Html->scriptBlock(
  'import {userIndex} from "./js/user-index.js";'.
  'userIndex.init("'.ADD_WORKSHOP.'","'.$this->Url->build($this->url(UserController::WORKSHOPS)).'");',
  [
    'block' => 'scriptBottom',
    'type' => 'module',
  ]
);
foreach ($eventWorkshops as $eventWorkshop) {
  $eventWorkshops[$eventWorkshop->id] = $eventWorkshop;
}

?>
<?= $this->Styling->title(__('Welcome {0}', $this->userName())) ?>
<?= $this->element('messages') ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->button(
  __('Edit profile'),
  ButtonColorEnum::PRIMARY,
  [
    HtmlAction::SHOW_DIALOG => '#'.EDIT_PROFILE,
  ],
) ?>
<?= $this->Styling->button(
  __('Change password'),
  ButtonColorEnum::PRIMARY,
  [
    HtmlAction::SHOW_DIALOG => '#'.CHANGE_PASSWORD,
  ],
) ?>
<?= $this->Styling->linkButton(__('Logout'), AccountController::LOGOUT) ?>
<?php if ($this->isAdministrator()) : ?>
  <?= $this->Styling->linkButton(__('Administrator page'), AdministratorController::INDEX,
    ButtonColorEnum::SECONDARY) ?>
<?php endif; ?>
<?= $this->Styling->endPageButtons() ?>
<?php if ($event == null) : ?>
  <?= $this->Styling->textBlock(__('At the moment there are no planned events.')) ?>
<?php elseif (!$event->hasActiveSignup()) : ?>
  <?= $this->Styling->textBlock(
    __(
      'There is no active signup. The next {0} event will take place at {1}.',
      Configure::read('Custom.eventName'), $event->getEventDateAsText(),
    )
  ) ?>
<?php else :
  $introduction = match ($event->participant_type) {
    ParticipantType::CHILDREN =>
    __(
      'You can now sign up your child(ren) for the {0} event at {1}.',
      Configure::read('Custom.eventName'),
      $event->getEventDateAsText(),
    ),
    default =>
    __(
      'You can now sign up yourself and/or other participants for the {0} event at {1}.',
      Configure::read('Custom.eventName'),
      $event->getEventDateAsText(),
    ),
  };
  ?>
  <?= $this->Styling->textBlock($introduction) ?>
  <?php if (!empty($participants)) : ?>
  <div class="cd-participants__container">
    <?php foreach ($participants as $participant): ?>
      <div class="cd-participant__row"></div>
      <div class="cd-participant__name">
        <?= $this->Styling->smallTitle($participant->name) ?>
        <div class="cd-participant__actions cd-participant__actions--at-name">
          <?= $this->Styling->iconButton(
            ButtonIconEnum::EDIT,
            ButtonColorEnum::PRIMARY,
            [
              HtmlAction::SHOW_DIALOG => '#'.EDIT_PARTICIPANT,
              HtmlData::PARTICIPANT_ID => $participant->id,
              HtmlData::PARTICIPANT_NAME => $participant->name,
              HtmlData::PARTICIPANT_HAS_LAPTOP => $participant->has_laptop ? '1' : '0',
              HtmlData::PARTICIPANT_CAN_LEAVE => $participant->can_leave ? '1' : '0',
            ]
          ) ?>
          <?= $this->Styling->iconButton(
            ButtonIconEnum::REMOVE,
            ButtonColorEnum::DANGER,
            [
              HtmlAction::SHOW_DIALOG => '#'.REMOVE_PARTICIPANT,
              HtmlData::PARTICIPANT_ID => $participant->id,
              HtmlData::PARTICIPANT_NAME => $participant->name,
            ]
          ) ?>
        </div>
      </div>
      <div class="cd-participant__first-workshop">
        <?= $this->element('workshop_cell', [
          'participant' => $participant,
          'eventWorkshop' => $participant->event_workshop_1_id == null
            ? null
            : $eventWorkshops[$participant->event_workshop_1_id],
          'enable' => true,
          'addDialog' => ADD_WORKSHOP,
          'replaceDialog' => ADD_WORKSHOP,
          'removeDialog' => REMOVE_WORKSHOP,
          'index' => WorkshopIndex::FIRST,
          'buttons' => true,
        ]) ?>
      </div>
      <div class="cd-participant__backup-workshop">
        <?= $this->element('workshop_cell', [
          'participant' => $participant,
          'eventWorkshop' => $participant->event_workshop_2_id == null
            ? null
            : $eventWorkshops[$participant->event_workshop_2_id],
          'enable' =>
            ($participant->event_workshop_1_id != null) &&
            ($eventWorkshops[$participant->event_workshop_1_id]->getWaitingPosition($participant) > 0),
          'addDialog' => ADD_WORKSHOP,
          'replaceDialog' => ADD_WORKSHOP,
          'removeDialog' => REMOVE_WORKSHOP,
          'index' => WorkshopIndex::BACKUP,
          'buttons' => true,
        ]) ?>
      </div>
      <div class="cd-participant__actions cd-participant__actions--at-end">
        <?= $this->Styling->iconButton(
          ButtonIconEnum::EDIT,
          ButtonColorEnum::PRIMARY,
          [
            HtmlAction::SHOW_DIALOG => '#'.EDIT_PARTICIPANT,
            HtmlData::PARTICIPANT_ID => $participant->id,
            HtmlData::PARTICIPANT_NAME => $participant->name,
            HtmlData::PARTICIPANT_HAS_LAPTOP => $participant->has_laptop ? '1' : '0',
            HtmlData::PARTICIPANT_CAN_LEAVE => $participant->can_leave ? '1' : '0',
          ]
        ) ?>
        <?= $this->Styling->iconButton(
          ButtonIconEnum::REMOVE,
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.REMOVE_PARTICIPANT,
            HtmlData::PARTICIPANT_ID => $participant->id,
            HtmlData::PARTICIPANT_NAME => $participant->name,
          ]
        ) ?>
      </div>
    <?php endforeach; ?>
    <div class="cd-participant__row"></div>
  </div>
<?php endif; ?>
  <div>
    <?= $this->Styling->bigButton(
      $event->participant_type == ParticipantType::CHILDREN
        ? __('Add child')
        : __('Add participant'),
      ButtonColorEnum::PRIMARY,
      [
        HtmlAction::SHOW_DIALOG => '#'.ADD_PARTICIPANT,
        HtmlData::PARTICIPANT_NAME => '',
        HtmlData::PARTICIPANT_HAS_LAPTOP => false,
        HtmlData::PARTICIPANT_CAN_LEAVE => false,
      ],
    ) ?>
  </div>
  <?php if (!empty($participants)) : ?>
  <?= $this->element(
    'dialog/edit_participant', ['data' => $editParticipantData, 'id' => EDIT_PARTICIPANT]
  ) ?>
  <?= $this->element(
    'dialog/remove_participant_from_user',
    [
      'data' => new IdViewModel(),
      'id' => REMOVE_PARTICIPANT,
      'postUrl' => UserController::REMOVE_PARTICIPANT,
    ]
  ) ?>
  <?= $this->element(
    'dialog/select_participant_workshop',
    [
      'data' => new SelectWorkshopViewModel(),
      'id' => ADD_WORKSHOP
    ]
  ) ?>
  <?= $this->element(
    'dialog/remove_participant_workshop',
    [
      'data' => new RemoveWorkshopViewModel(),
      'id' => REMOVE_WORKSHOP
    ]
  ) ?>
<?php endif; ?>
  <?= $this->element(
  'dialog/add_participant', ['data' => $addParticipantData, 'id' => ADD_PARTICIPANT]
) ?>
<?php endif; ?>
<?= $this->element(
  'dialog/edit_profile', ['data' => $editProfileData, 'id' => EDIT_PROFILE]
) ?>
<?= $this->element(
  'dialog/change_password', ['data' => $changePasswordData, 'id' => CHANGE_PASSWORD]
) ?>
