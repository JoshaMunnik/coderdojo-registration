<?php

use App\Controller\UsersController;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ButtonColorEnum;
use App\View\ApplicationView;

/**
 * @var UserEntity $user
 * @var ApplicationView $this
 */

?>
<?= $this->Styling->title(__('QR code for {0}', $user->name)) ?>
<?= $this->Styling->beginPageButtons() ?>
<?= $this->Styling->linkButton(
  __('Back to users'), UsersController::INDEX, ButtonColorEnum::SECONDARY
) ?>
<?= $this->Styling->endPageButtons() ?>
<div>
  <img src="<?= $user->getQRCodeImage() ?>" alt="QR Code" />
</div>
