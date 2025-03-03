<?php

use App\Controller\AccountController;
use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 */

?>
<?= $this->Styling->title(__('Welcome')) ?>
<?= $this->Styling->textBlock(__('To login or register click:')) ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(__('Login or register'), $this->url(AccountController::LOGIN)) ?>
<?= $this->Styling->endPageButtons() ?>
