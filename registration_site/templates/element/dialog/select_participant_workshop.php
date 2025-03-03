<?php

use App\Controller\UserController;
use App\Model\Constant\HtmlData;
use App\Model\Enum\ButtonColorEnum;
use App\Model\View\User\SelectWorkshopViewModel;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var SelectWorkshopViewModel $data
 * @var string $id
 */

$participantName = '<span '.HtmlData::PARTICIPANT_NAME.'></span>';

?>
<?= $this->Styling->beginFormDialog(
  $id,
  __('Select workshop'),
  $data,
  [UserController::SELECT_WORKSHOP],
  [
    SelectWorkshopViewModel::PARTICIPANT_ID => [
      HtmlData::PARTICIPANT_ID,
      'id' => 'workshop-card-participant-id'
    ],
    SelectWorkshopViewModel::WORKSHOP_ID => [
      HtmlData::WORKSHOP_ID,
      'id' => 'workshop-card-workshop-id'
    ],
    SelectWorkshopViewModel::INDEX => HtmlData::WORKSHOP_INDEX,
  ]
) ?>
<div class="cd-workshop-card__container" id="workshop-cards-container">
  <div
    class="cd-workshop-card__centered-text"
    id="workshop-card-loading"
  >
    <?= $this->Styling->text(__("Loading workshops...")) ?>
  </div>
  <div
    class="cd-workshop-card__centered-text cd-workshop-card__centered-text--is-hidden"
    id="workshop-card-none"
  >
    <?= $this->Styling->smallTitle(__("No workshops")) ?>
  </div>
</div>
<div class="cd-workshop-card__buttons">
  <?= $this->Styling->button(
    __('Previous'),
    ButtonColorEnum::PRIMARY,
    [
      'id' => 'workshop-cards-previous-button',
    ]
  ) ?>
  <?= $this->Styling->button(
    __('Next'),
    ButtonColorEnum::PRIMARY,
    [
      'id' => 'workshop-cards-next-button',
    ]
  ) ?>
</div>
<?= $this->Styling->beginDialogButtons() ?>
<?= $this->Styling->submit(
  __('Select'),
  ButtonColorEnum::DANGER,
  '',
  [
    'id' => 'workshop-card-submit-button',
  ]
) ?>
<?= $this->Styling->closeButton(__('Cancel')) ?>
<?= $this->Styling->endDialogButtons() ?>
<?= $this->Styling->endFormDialog() ?>
<template id="workshop-card-template">
  <div class="cd-workshop-card__card" data-workshop-id="">
    <div class="cd-workshop-card__name" data-workshop-name>
    </div>
    <div class="cd-workshop-card__description" data-workshop-description>
    </div>
    <div
      class="cd-workshop-card__info cd-workshop-card__info--is-available"
      data-workshop-available
    >
      <?= $this->Styling->text(__('Available')) ?>
    </div>
    <div
      class="cd-workshop-card__info cd-workshop-card__info--has-waiting-list"
      data-workshop-waiting
    >
      <?= $this->Styling->text(
        __('Full, waiting list: {0}', '<span data-workshop-waiting-value></span>')
      ) ?>
    </div>
    <div
      class="cd-workshop-card__info cd-workshop-card__info--has-waiting-list"
      data-workshop-full
    >
      <?= $this->Styling->text(
        __('Full')
      ) ?>
    </div>
  </div>
</template>
