<?php

namespace App\Model\Data;

use App\Model\Entity\EventEntity;
use Cake\I18n\DateTime;

/**
 * A structure that adds count information to an event.
 */
readonly class EventWithCountsData
{
  #region constructors

  public function __construct(
    public EventEntity $event,
    public int $participatingCount,
    public int $waitingCount,
    public int $workshopsCount,
    public int $placesCount,
  ) {
  }

  #endregion
}
