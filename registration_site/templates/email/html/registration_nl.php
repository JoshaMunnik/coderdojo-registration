<?php

use App\Controller\UserController;
use App\Model\Entity\UserEntity;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var UserEntity $user
 */

?>
<h2>Best <?= $user->name ?></h2>
<p>Welkom op de registratie site.</p>
<p>
  <?= $this->Html->link("Bezoek site", $this->url([UserController::INDEX, '_full' => true])) ?>
</p>
