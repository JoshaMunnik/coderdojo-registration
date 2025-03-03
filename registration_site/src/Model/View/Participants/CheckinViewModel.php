<?php

namespace App\Model\View\Participants;

use App\Lib\Model\View\ViewModelBase;

/**
 * A view model to checkin a participant.
 */
class CheckinViewModel extends ViewModelBase
{
  #region public fields

  public string $participant_id;

  public bool $checked_in;

  #endregion
}
