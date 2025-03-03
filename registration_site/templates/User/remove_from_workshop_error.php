<?php

use App\View\ApplicationView;

/**
 * @var ApplicationView $this
 */

?>
<?= $this->Styling->title(__('Error')) ?>
<?= $this->Styling->textBlock(
  __('An error occurred while trying to remove the participant from the workshop.')
) ?>
