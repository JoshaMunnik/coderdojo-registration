<?php

namespace App\Model\View\User;

use App\Lib\Model\View\ViewModelBase;

/**
 * {@link RemoveFromWorkshopViewModel} is the view model for removing a participant from a workshop.
 */
class RemoveFromWorkshopViewModel extends ViewModelBase
{
  #region field constants

  public const PARTICIPANT_ID = 'participant_id';
  public const EVENT_WORKSHOP_ID = 'event_workshop_id';

  #endregion

  #region fields

  public string $participant_id = '';

  public string $event_workshop_id = '';

  #endregion
}
