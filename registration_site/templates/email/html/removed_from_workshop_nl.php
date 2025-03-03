<?php

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
 */

?>
<h2>Best <?= $user->name ?></h2>
<p>
  De deelnemer <?= $participant->name ?> is verwijderd van de
  workshop <?= $eventWorkshop->getName() ?> voor het <?= Configure::read('Custom.eventName') ?>
  evenement op <?= $event->getEventDateAsText() ?>.
</p>

