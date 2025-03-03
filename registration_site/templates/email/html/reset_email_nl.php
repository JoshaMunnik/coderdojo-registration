<?php

use App\Controller\AccountController;
use App\Model\Entity\UserEntity;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var UserEntity $user
 * @var string $token
 */

?>
<h2>Best <?= $user->name ?></h2>
<p>Om een nieuw wachtwoord in te stellen, bezoek de volgende link:</p>
<p>
  <?= $this->Html->link(
    "Nieuw wachtwoord", $this->url([AccountController::RESET_PASSWORD, $token, '_full' => true]),
  ) ?>
</p>
