<?php

namespace App\Lib\Model\Entity;

use DateTime;

/**
 * Adds a {@link created} field. Use this interface in combination with {@link TableWithTimestamp}.
 *
 * @property DateTime $created Date and time record was created
 */
interface IEntityWithCreated
{
    #region column names

    public const CREATED = 'created';

    #endregion
}
