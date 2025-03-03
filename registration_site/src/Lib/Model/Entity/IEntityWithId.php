<?php

namespace App\Lib\Model\Entity;

/**
 * {@link IEntityWithId} adds an {@link id} column. Assumes the id is an uuid.
 *
 * @property string $id Id of record
 */
interface IEntityWithId
{
  #region column names

  public const ID = 'id';

  #endregion
}
