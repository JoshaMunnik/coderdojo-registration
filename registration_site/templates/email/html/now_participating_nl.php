<?php

use App\Controller\UserController;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\View\ApplicationView;
use Cake\Core\Configure;

/**
 * @var ApplicationView $this
 * @var UserEntity $user
 * @var ParticipantEntity $participant
 * @var EventEntity $event
 * @var EventWorkshopEntity $eventWorkshop
 * @var bool $isBackup
 */
?>
<h2>Beste <?= $user->name ?></h2>
<p>
  Goed nieuws, er is een plek vrijgekomen voor de workshop <?= $eventWorkshop->getName() ?> voor
  het <?= Configure::read('Custom.eventName') ?> evenement op <?= $event->getEventDateAsText() ?>.
  <br/>
  De deelnemer <?= $participant->name ?> kan nu deelnemen aan de workshop.
</p>
<?php if (!$isBackup) : ?>
  <p>
    Mocht de deelnemer ook zijn opgegeven voor een reserve workshop, dan is die deelname
    automatisch geannuleerd.
  </p>
<?php endif; ?>
<p>
  Om deelname te annuleren, klik op de volgende link:
</p>
<p>
  <?= $this->Html->link(
    "Verwijder deelnemer",
    $this->url(
      [UserController::REMOVE_FROM_WORKSHOP, $eventWorkshop->id, $participant->id, '_full' => true]
    ),
  ) ?>
</p>
<p>Om de deelname(s) te beheren, bezoek de volgende link:</p>
<p>
  <?= $this->Html->link(
    "Beheer deelname",
    $this->url([UserController::INDEX, '_full' => true]),
  ) ?>
</p>
