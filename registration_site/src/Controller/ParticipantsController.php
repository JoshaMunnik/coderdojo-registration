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
use App\Model\View\Participants\ScanViewModel;
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
  public const SCAN = [self::class, 'scan'];
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
    $event = Tables::events()->getForId($id);
    $this->set('event', $event);
    $this->set('participants', Tables::participants()->getAllForEventWithUser($event));
    $this->set('eventWorkshops', Tables::eventWorkshops()->getAllForEvent($event));
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
    $event = Tables::events()->getForId($id);
    $this->set('event', $event);
    $this->set('participants', Tables::participants()->getAllParticipatingForEventWithUser($event));
    $this->set('eventWorkshops', Tables::eventWorkshops()->getAllForEvent($event));
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
    if (Tables::participants()->checkin($participant, $viewData->checked_in)) {
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
    $participants = Tables::participants()->getAllForEventWithUser($event);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($event);
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
        $this->getWorkshopParticipatingInformation(
          $participant, $participant->event_workshop_1_id, $eventWorkshops
        ),
        $this->getWorkshopParticipatingInformation(
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

  /**
   * Shows a page to scan visitors QR codes for an event.
   *
   * @param string $id The id of the event.
   *
   * @return Response|null
   */
  public function scan(string $id): ?Response
  {
    $event = Tables::events()->getForId($id);
    $users = Tables::users()->getAllUsersWithParticipants($event);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($event);
    $this->set('event', $event);
    $this->set('workshops', $eventWorkshops);
    $this->set('users', $users);
    return null;
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
  private function getWorkshopParticipatingInformation(
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

  /**
   * Groups the participants by their user and determine the workshop they are participating in.
   *
   * The returned array has the following structure:
   * ````php
   * [user->id] => [
   *   'name' => string,
   *   'phone' => string,
   *   'public_id' => string,
   *   'participants' => [
   *     [
   *       'id' => string,
   *       'name' => string,
   *       'workshop' => string,
   *       'checked_in' => bool,
   *     ],
   *   ],
   * ]
   * ````
   *
   * @param array $participants
   * @param array $eventWorkshops
   *
   * @return array
   */
  private function getParticipatingUsers(array $participants, array $eventWorkshops): array
  {
    $result = [];
    foreach ($participants as $participant) {
      if ($participant->user === null) {
        continue;
      }
      $workshop = $this->getWorkshopDescription($participant, $eventWorkshops);
      if (!isset($result[$participant->user->id])) {
        $result[$participant->user->id] = [
          'name' => $participant->user->name,
          'phone' => $participant->user->phone,
          'public_id' => $participant->user->public_id,
          'participants' => [],
        ];
      }
      $result[$participant->user->public_id]['participants'][] = [
        'id' => $participant->id,
        'name' => $participant->name,
        'workshop' => $workshop,
        'checked_in' => $participant->checkin_date !== null,
      ];
    }
    return $result;
  }

  #endregion
}
