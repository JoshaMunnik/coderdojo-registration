<?php

namespace App\Model\View;

use App\Lib\Model\View\ViewModelBase;

/**
 * A view model to handle the id of some entity.
 */
class IdViewModel extends ViewModelBase
{
  #region field names

  public const ID = 'id';

  #endregion

  #region properties

  public string $id = '';

  #endregion

  #region public methods

  /**
   * @inheritDoc
   */
  public function clear(): void
  {
    $this->id = '';
  }

  /**
   * @inheritDoc
   */
  public function isNew(): bool
  {
    // existing data has a valid id with it
    return empty($this->id);
  }

  #endregion
}
