<?php

use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var array $params
 * @var string $message
 */

$class = 'cd-message';
if (!empty($params['class'])) {
    $class .= ' ' . $params['class'];
}
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="<?= $class ?>" onclick="this.classList.add('hidden');"><?= $message ?></div>
