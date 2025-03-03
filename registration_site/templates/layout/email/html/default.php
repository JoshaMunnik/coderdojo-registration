<?php

use Cake\I18n\I18n;

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="<?= I18n::getLocale() ?>">
<head>
    <title><?= $this->fetch('title') ?></title>
</head>
<body>
    <?= $this->fetch('content') ?>
</body>
</html>
