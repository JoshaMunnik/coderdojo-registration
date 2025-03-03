<?php
/**
 * @var ApplicationView $this
 */

use App\Model\Entity\UserEntity;
use App\View\ApplicationView;
use Cake\Core\Configure;
use Cake\I18n\I18n;

$user = $this->request->getAttribute('identity');
$adminMenu = $user && $user->get(UserEntity::ADMINISTRATOR);

?>
<!DOCTYPE html>
<html lang="<?= I18n::getLocale() ?>">
  <head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
      <?= __('{0} Signup', Configure::read('Custom.eventName')) ?> | <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css([
      'normalize.min',
      'fonts',
      'site',
      '../fontawesome/css/fontawesome.min.css',
      '../fontawesome/css/all.min.css',
    ]) ?>
    <!-- <script src="https://cdn.jsdelivr.net/gh/JoshaMunnik/uf-html-helpers@master/dist/uf-html-helpers.js"></script> -->
    <?= $this->Html->script(['uf-html-helpers']) ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
  </head>
  <body>
    <main class="cd-main__container">
      <?= $this->element('language') ?>
      <?= $this->fetch('content') ?>
    </main>
    <?= $this->fetch('scriptBottom') ?>
  </body>
</html>
