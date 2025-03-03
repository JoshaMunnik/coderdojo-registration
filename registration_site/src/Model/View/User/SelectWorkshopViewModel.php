<?php

namespace App\Model\View\User;

use App\Lib\Model\View\ViewModelBase;

class SelectWorkshopViewModel extends RemoveWorkshopViewModel
{
  #region field constants

  public const WORKSHOP_ID = 'workshop_id';

  #endregion

  #region fields

  public string $workshop_id = '';

  #endregion
}
