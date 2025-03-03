<?php

use App\Controller\UserController;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\View\User\RemoveFromWorkshopViewModel;
use App\View\ApplicationView;
use Cake\Core\Configure;

/**
 * @var ApplicationView $this
 * @var ParticipantEntity $participant
 * @var EventWorkshopEntity $eventWorkshop
 * @var EventEntity $event
 */

$data = new RemoveFromWorkshopViewModel();
$data->participant_id = $participant->id;
$data->event_workshop_id = $eventWorkshop->id;

?>
<?= $this->Styling->title(__('Confirm')) ?>
<?= $this->Styling->textBlock(
  __(
    'Remove {0} from workshop {1} for {2} event at {3}.',
    $participant->name,
    $eventWorkshop->getName(),
    Configure::read('Custom.eventName'),
    $event->getEventDateAsText()
  )
) ?>
<?= $this->createForm($data, UserController::CONFIRM_REMOVE_FROM_WORKSHOP) ?>
<?= $this->Form->hidden(RemoveFromWorkshopViewModel::PARTICIPANT_ID) ?>
<?= $this->Form->hidden(RemoveFromWorkshopViewModel::EVENT_WORKSHOP_ID) ?>
<?= $this->Styling->bigSubmit(__('Yes, remove')) ?>
<?= $this->Form->end() ?>
