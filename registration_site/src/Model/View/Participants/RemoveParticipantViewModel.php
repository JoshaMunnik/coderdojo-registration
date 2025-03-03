<?php

namespace App\Model\View\Participants;

use App\Model\View\IdViewModel;

/**
 * A view model to remove a participant.
 */
class RemoveParticipantViewModel extends IdViewModel
{
  #region field constants

  public const IS_CHECKIN = 'is_checkin';
  public const EVENT_ID = 'event_id';

  #endregion

  #region public fields

  /**
   * Determines which page the remove action was performed at.
   *
   * @var bool True if the remove action was performed at the checkin page.
   */
  public bool $is_checkin = false;

  public string $event_id = '';

  #endregion

  #region constructors

  public function __construct(bool $isCheckin, string $eventId)
  {
    parent::__construct();
    $this->is_checkin = $isCheckin;
    $this->event_id = $eventId;
  }

  #endregion
}
