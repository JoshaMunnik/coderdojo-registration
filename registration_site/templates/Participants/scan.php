<?php

use App\Controller\EventsController;
use App\Controller\ParticipantsController;
use App\Model\Constant\HtmlData;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\UserWithParticipantsEntity;
use App\Model\Enum\ButtonColorEnum;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var UserWithParticipantsEntity[] $users
 * @var EventWorkshopEntity[] $workshops
 * @var EventEntity $event
 */

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
$this->Html->scriptBlock(
  'import {participantsScan} from "./js/participants-scan.js";'.
  'participantsScan.init();',
  [
    'block' => 'scriptBottom',
    'type' => 'module',
  ]
);
?>
<?= $this->Styling->title(__('Scanning for {0}', $event->getEventDateAsText())) ?>
<?= $this->Styling->beginRow() ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Back to events'), EventsController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<?= $this->Styling->endRow() ?>
<div class="cd-scan__section" id="webcam-section">
  <select class="cd-form__drop-down" id="webcam-select">
    <option>Getting cameras...</option>
  </select>
  <video class="cd-scan__webcam-video" id="webcam-view"></video>
  <div class="cd-scan__message" id="waiting-message">
    <?= __('Waiting for QR code...') ?>
  </div>
  <div
    class="cd-scan__message cd-scan__message--is-error cd-scan__message--is-hidden"
    id="unknown-message"
  >
    <?= __('Unknown QR code.') ?>
  </div>
</div>
<div class="cd-scan__section cd-scan__section--is-hidden" id="user-section">
  <?php foreach ($users as $user) : ?>
    <div
      class="cd-scan__user-container cd-scan__user-container--is-hidden"
      data-user-id="<?= $user->public_id ?>"
    >
      <div class="cd-scan__user-information">
        <div class="cd-scan__user-name">
          <?= $user->name ?>
        </div>
        <div class="cd-scan__user-phone">
          <?= $user->phone ?>
        </div>
      </div>
      <div class="cd-scan__participants-container">
        <?php foreach ($user->participants as $participant) : ?>
          <div class="cd-scan__participant-container">
            <div class="cd-scan__participant-information">
              <div class="cd-scan__participant-name">
                <?= $participant->name ?>
              </div>
              <div class="cd-scan__participant-workshop">
                <?= $participant->getWorkshopDescription($workshops) ?>
              </div>
              <div class="cd-scan__participant-status">
                <?= $participant->can_leave ? __('can leave') : __('will be picked up') ?>
              </div>
            </div>
            <div class="cd-scan__button-container">
              <?= $this->Styling->toggleButton(
                $participant->checkin_date !== null,
                __('yes'),
                __('no'),
                [
                  HtmlData::PARTICIPANT_ID => $participant->id,
                  HtmlData::CHECKIN_BUTTON,
                ]
              ) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="cd-scan__buttons">
    <?= $this->Styling->button(
      __('Next'),
      ButtonColorEnum::PRIMARY,
      [
        'id' => 'next-button',
      ]
    ) ?>
  </div>
</div>
