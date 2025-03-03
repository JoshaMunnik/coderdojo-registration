<?php

namespace App\Model\Entity\Field;

use App\Model\Entity\UserEntity;

/**
 * Defines a reference to a user entity.
 *
 * @property string $user_id
 *
 * @property UserEntity $user
 */
interface IUser
{
  const USER_ID = 'user_id';
}
