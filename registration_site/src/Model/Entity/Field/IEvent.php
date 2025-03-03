<?php

namespace App\Model\Entity\Field;

use App\Model\Entity\EventEntity;

/**
 * Defines an reference to the an event entity.
 *
 * @property string $event_id
 *
 * @property EventEntity $event
 */
interface IEvent
{
  const EVENT_ID = 'signup_event_id';
}
