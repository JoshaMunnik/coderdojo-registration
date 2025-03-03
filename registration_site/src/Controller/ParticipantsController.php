<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Tables;
use App\Model\Value\Language;
use App\Model\View\Participants\CheckinViewModel;
use App\Model\View\Participants\RemoveParticipantViewModel;
use App\Tool\FileTool;
use App\Tool\ParticipantTool;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\View\JsonView;
use DateTime;

/**
 * {@link ParticipantsController} manages the participants for an event.
 */
class ParticipantsController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const MANAGE_CHECKIN = [self::class, 'manage-checkins'];
  public const CHECKIN = [self::class, 'checkin'];
  public const DOWNLOAD = [self::class, 'download'];
  public const REMOVE = [self::class, 'remove'];

  #endregion

  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function beforeFilter(EventInterface $event): void
  {
    parent::beforeFilter($event);
    // checkin is a ajax action with a post request, the protection should ignore this action
    $this->FormProtection->setConfig('unlockedActions', ['checkin']);
  }

  #endregion

  #region public methods

  /**
   * Shows the list of participants for a certain event and their selected workshops.
   *
   * @param string $id The id of event.
   */
  public function index(string $id): ?Response
  {
    $this->set('event', Tables::events()->getForId($id));
    $this->set('participants', Tables::participants()->getAllForEventWithUser($id));
    $this->set('eventWorkshops', Tables::eventWorkshops()->getAllForEvent($id));
    return null;
  }

  /**
   * Shows the list of participants so they can get checked in.
   *
   * @param string $id The id of event.
   *
   * @return Response|null
   */
  public function manageCheckins(string $id): ?Response
  {
    $this->set('event', Tables::events()->getForId($id));
    $this->set(
      'participants',
      Tables::participants()->getAllParticipatingForEventWithUser($id)
    );
    $this->set('eventWorkshops', Tables::eventWorkshops()->getAllForEvent($id));
    return null;
  }

  /**
   * This action should be called via AJAX to check in a participant.
   *
   * It returns the following json structure:
   *
   * ```
   * {
   *  "success": bool,
   *  "checked_in": bool
   * }
   * ```
   *
   * @return Response|null
   */
  public function checkin(): ?Response
  {
    // return response as json
    $this->set('root', ['success' => true, 'checked_in' => null]);
    $this->viewBuilder()
      ->setClassName(JsonView::class)
      ->setOption('serialize', 'root');
    if (!$this->isSubmit()) {
      return null;
    }
    $viewData = new CheckinViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return null;
    }
    $participant = Tables::participants()->getForId($viewData->participant_id);
    $previousCheckInData = $participant->checkin_date;
    $participant->checkin_date = $viewData->checked_in ? new DateTime() : null;
    if (Tables::participants()->save($participant)) {
      $this->set('root', ['success' => true, 'checked_in' => $viewData->checked_in]);
    }
    else {
      $this->set('root', ['success' => false, 'checked_in' => $previousCheckInData !== null]);
    }
    return null;
  }

  /**
   * Removes a participant from the event.
   *
   * @return Response|null
   */
  public function remove(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new RemoveParticipantViewModel(false, '');
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(
        [$viewData->is_checkin ? self::MANAGE_CHECKIN : self::INDEX, $viewData->event_id]
      );
    }
    $participant = Tables::participants()->getForId($viewData->id);
    if ($participant->event_id !== $viewData->event_id) {
      return $this->redirectWithError(
        [$viewData->is_checkin ? self::MANAGE_CHECKIN : self::INDEX, $viewData->event_id],
        __('Participant {0} does not belong to this event.', $participant->name)
      );
    }
    ParticipantTool::deleteParticipant($participant);
    return $this->redirectWithSuccess(
      [$viewData->is_checkin ? self::MANAGE_CHECKIN : self::INDEX, $participant->event_id],
      __('Participant {0} has been removed.', $participant->name)
    );
  }

  /**
   * Download a CSV file with the participants for an event.
   *
   * @param string $id
   *
   * @return Response
   */
  public function download(string $id): Response
  {
    $event = Tables::events()->getForId($id);
    $participants = Tables::participants()->getAllForEventWithUser($id);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($id);
    $headers = [
      __('Participant name'),
      __('User email'),
      __('User name'),
      __('First workshop'),
      __('Backup workshop'),
      __('Language'),
      __('Laptop'),
      __('Participated'),
    ];
    $data = [];
    foreach ($participants as $participant) {
      $data[] = [
        $participant->name,
        $participant->user?->email ?? '-',
        $participant->user?->name ?? '-',
        $this->getWorkshopInformation(
          $participant, $participant->event_workshop_1_id, $eventWorkshops
        ),
        $this->getWorkshopInformation(
          $participant, $participant->event_workshop_2_id, $eventWorkshops
        ),
        $participant->user != null ? Language::getName($participant->user->language_id) : '',
        $participant->has_laptop ? __('yes') : '',
        $this->getParticipatedStatus($event, $participant, $eventWorkshops),
      ];
    }
    return $this->exportCsv(
      FileTool::addDate('participants.csv', $event->event_date->toNative()), $data, $headers
    );
  }

  #endregion

  #region private functions

  /**
   * Gets the name and position in the waiting queue (if any) for a workshop.
   *
   * @param ParticipantEntity $participant
   * @param string|null $workshopId
   * @param EventWorkshopEntity[] $eventWorkshops
   *
   * @return string
   */
  private function getWorkshopInformation(
    ParticipantEntity $participant,
    string|null $workshopId,
    array $eventWorkshops
  ): string {
    if ($workshopId === null) {
      return '';
    }
    $eventWorkshop = $eventWorkshops[$workshopId];
    $position = $eventWorkshop->getWaitingPosition($participant);
    if ($position === 0) {
      return $eventWorkshop->getName().' ('.__('participating').')';
    }
    elseif ($position > 0) {
      return $eventWorkshop->getName().' ('.__('waiting position: {0}', $position).')';
    }
    else {
      return $eventWorkshop->getName().' ('.__('NOT participating').')';
    }
  }

  /**
   * Checks if the participant has participated in the event. The method will return 'no' if the
   * participated was not in the waiting queue and did not show up during the event.
   *
   * @param EventEntity $event
   * @param ParticipantEntity $participant
   * @param EventWorkshopEntity[] $eventWorkshops
   *
   * @return string
   */
  private function getParticipatedStatus(
    EventEntity $event,
    ParticipantEntity $participant,
    array $eventWorkshops
  ): string {
    if (!$event->isFinished()) {
      return '';
    }
    if ($participant->checkin_date) {
      return __('yes');
    }
    if ($participant->isParticipating($eventWorkshops)) {
      return __('no');
    }
    return '';
  }

  #endregion
}
