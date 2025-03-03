<?php

use App\Model\Constant\HtmlAction;
use App\Model\Constant\HtmlData;
use App\Model\Constant\WorkshopIndex;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Enum\ButtonColorEnum;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var ParticipantEntity $participant
 * @var EventWorkshopEntity|null $eventWorkshop
 * @var bool $enable
 * @var string $addDialog
 * @var string $replaceDialog
 * @var string $removeDialog
 * @var int $index
 * @var bool $buttons
 */

$waitingPosition = $eventWorkshop?->getWaitingPosition($participant) ?? false;
$extraContainer = $buttons ? 'cd-workshop-cell__container--has-buttons' : '';
?>
<?php if (($eventWorkshop == null) && $enable && $buttons) : ?>
  <div class="cd-workshop-cell__container">
    <?= $this->Styling->button(
      $index === WorkshopIndex::FIRST
        ? __('Add workshop')
        : __('Add backup workshop'),
      ButtonColorEnum::PRIMARY,
      [
        HtmlAction::SHOW_DIALOG => '#'.$addDialog,
        HtmlData::PARTICIPANT_ID => $participant->id,
        HtmlData::PARTICIPANT_NAME => $participant->name,
        HtmlData::WORKSHOP_INDEX => $index,
      ],
    ) ?>
  </div>
<?php elseif (($eventWorkshop == null) && !$enable && $buttons) : ?>
  <div class="cd-workshop-cell__container">
    <?= $this->Styling->staticButton(
      $index === WorkshopIndex::FIRST
        ? __('Add workshop')
        : __('Add backup workshop'),
    ) ?>
  </div>
<?php else : ?>
  <div class="cd-workshop-cell__container <?= $extraContainer ?>">
    <?php if ($buttons) : ?>
      <?= $this->Styling->smallTitle($eventWorkshop->getName()) ?>
    <?php else: ?>
      <?= $this->Styling->text($eventWorkshop->getName()) ?>
    <?php endif; ?>
    <?php if ($waitingPosition == 0) : ?>
      <span class="cd-text--is-success"><?= __('Participating') ?></span>
    <?php elseif ($waitingPosition < 0) : ?>
      <span class="cd-text--is-danger"><?= __('Not participating') ?></span>
    <?php else : ?>
      <span class="cd-text--is-danger">
        <?= __('Waiting position: {0}', $waitingPosition) ?>
      </span>
    <?php endif; ?>
    <?php if ($buttons) : ?>
      <div class="cd-workshop-cell__buttons">
        <?= $this->Styling->button(
          __('Choose other'),
          ButtonColorEnum::PRIMARY,
          [
            HtmlAction::SHOW_DIALOG => '#'.$replaceDialog,
            HtmlData::WORKSHOP_ID => $eventWorkshop->id,
            HtmlData::WORKSHOP_NAME => $eventWorkshop->getName(),
            HtmlData::PARTICIPANT_ID => $participant->id,
            HtmlData::PARTICIPANT_NAME => $participant->name,
          ],
        ) ?>
        <?= $this->Styling->button(
          __('Remove'),
          ButtonColorEnum::DANGER,
          [
            HtmlAction::SHOW_DIALOG => '#'.$removeDialog,
            HtmlData::WORKSHOP_ID => $eventWorkshop->id,
            HtmlData::WORKSHOP_NAME => $eventWorkshop->getName(),
            HtmlData::PARTICIPANT_ID => $participant->id,
            HtmlData::PARTICIPANT_NAME => $participant->name,
            HtmlData::WORKSHOP_INDEX => $index,
          ],
        ) ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>
