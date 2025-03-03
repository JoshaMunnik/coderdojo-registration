<?php

use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 * @var array $params
 * @var string $message
 */

if (!isset($params['escape']) || $params['escape'] !== false) {
  $message = h($message);
}
?>
<div class="cd-message cd-message--is-error">
  <?= $message ?>
</div>
