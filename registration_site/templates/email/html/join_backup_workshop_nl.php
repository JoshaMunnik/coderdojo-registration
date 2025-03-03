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
 * @var int $position
 * @var EventWorkshopEntity $firstEventWorkshop
 * @var EventWorkshopEntity $backupEventWorkshop
 */
?>
<h2>Best <?= $user->name ?></h2>
<p>
  De deelnemer <?= $participant->name ?> is aangemeld voor de reserve workshop
  <?= $backupEventWorkshop->getName() ?> voor het
  <?= Configure::read('Custom.eventName') ?> evenement op <?= $event->getEventDateAsText() ?>.
</p>
<p>
  Mocht er een plek vrijkomen in de workshop <?= $firstEventWorkshop->getName() ?>, dan wordt de
  deelnemer automatisch verplaatst naar deze workshop. En wordt er een bevestiging e-mail gestuurd.
</p>
<?php if ($position >= $backupEventWorkshop->place_count) : ?>
  <p>
    De deelnemer is op de wachtlijst geplaatst. Mocht er een plek vrijkomen, dan wordt er
    een bevestiging e-mail gestuurd.
  </p>
<?php endif; ?>
<p>
  Om deelname te annuleren, klik op de volgende link:
</p>
<p>
  <?= $this->Html->link(
    "Verwijder deelnemer",
    $this->url([UserController::REMOVE_FROM_WORKSHOP, $backupEventWorkshop->id, $participant->id]),
    ['_full' => true],
  ) ?>
</p>
<p>Om de deelname(s) te beheren, bezoek de volgende link:</p>
<p>
  <?= $this->Html->link(
    "Beheer deelname",
    $this->url([UserController::INDEX, '_full' => true]),
  ) ?>
</p>
