<?php

namespace App\Lib\Model\Entity;

use DateTime;

/**
 * Adds a {@link modified} date to {@link IEntityWithCreated} creating a full timestamp. Use
 * this interface in combination with {@link TableWithTimestamp}.
 *
 * @property DateTime $modified Date and time of last record update
 */
interface IEntityWithTimestamp extends IEntityWithCreated
{
  #region column names

  public const MODIFIED = 'modified';

  #endregion
}
