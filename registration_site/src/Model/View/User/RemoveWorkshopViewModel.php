<?php

namespace App\Model\View\User;

use App\Lib\Model\View\ViewModelBase;
use App\Model\Constant\WorkshopIndex;

/**
 * {@link RemoveWorkshopViewModel} is the view model for removing a workshop from a participant.
 */
class RemoveWorkshopViewModel extends ViewModelBase
{
  #region field constants

  public const PARTICIPANT_ID = 'participant_id';

  public const INDEX = 'index';

  #endregion

  #region fields

  public string $participant_id = '';

  /**
   * See {@link WorkshopIndex}
   *
   * @var int
   */
  public int $index = 0;

  #endregion
}
