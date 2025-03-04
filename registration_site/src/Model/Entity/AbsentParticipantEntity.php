<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithCreated;
use App\Lib\Model\Entity\IEntityWithId;
use Cake\ORM\Entity;

/**
 * {@link AbsentParticipantEntity} encapsulates a single absent participant in the database.
 *
 * @property string $user_id
 * @property string $event_id
 *
 * @property EventEntity $event
 * @property UserEntity $user
 */
class AbsentParticipantEntity extends Entity implements IEntityWithId, IEntityWithCreated
{
  #region field constants

  public const USER_ID = 'user_id';

  public const EVENT_ID = 'event_id';

  #endregion
}
