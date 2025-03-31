<?php

namespace App\Controller;

use App\Lib\Controller\ApplicationControllerBase;
use App\Model\Constant\WorkshopIndex;
use App\Model\Entity\EventEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Tables;
use App\Model\View\IdViewModel;
use App\Model\View\User\EditParticipantViewModel;
use App\Model\View\User\RemoveFromWorkshopViewModel;
use App\Model\View\User\RemoveWorkshopViewModel;
use App\Model\View\User\SelectWorkshopViewModel;
use App\Tool\ParticipantTool;
use Cake\Http\Response;
use Cake\View\JsonView;
use Exception;

/**
 * {@link UserController} manages the users profile and registration of participants for the
 * next event.
 */
class UserController extends ApplicationControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const REMOVE_PARTICIPANT = [self::class, 'remove-participant'];
  public const WORKSHOPS = [self::class, 'workshops'];
  public const SELECT_WORKSHOP = [self::class, 'select-workshop'];
  public const REMOVE_WORKSHOP = [self::class, 'remove-workshop'];
  public const REMOVE_FROM_WORKSHOP = [self::class, 'remove-from-workshop'];
  public const REMOVE_FROM_WORKSHOP_ERROR = [self::class, 'remove-from-workshop-error'];
  public const REMOVE_FROM_WORKSHOP_SUCCESS = [self::class, 'remove-from-workshop-success'];
  public const CONFIRM_REMOVE_FROM_WORKSHOP = [self::class, 'confirm-remove-from-workshop'];

  public const SUBMIT_ADD_PARTICIPANT = 'submit_add_participant';
  public const SUBMIT_EDIT_PARTICIPANT = 'submit_edit_participant';

  #endregion

  #region cakephp callbacks

  #endregion

  #region public methods

  /**
   * Shows the home page for the user and process form submissions from that page.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $event = Tables::events()->getNextEvent();
    $participants = $event != null
      ? Tables::participants()->getAllForUserAndEvent($this->user(), $event)
      : [];
    $eventWorkshops = $event != null
      ? Tables::eventWorkshops()->getAllForEventWithParticipants($event)
      : [];
    $this->set('editProfileData', $this->processEditProfile(self::INDEX));
    $this->set('changePasswordData', $this->processChangePassword(self::INDEX));
    $this->set('addParticipantData', $this->processAddParticipant($event));
    $this->set('editParticipantData', $this->processEditParticipant($event));
    $this->set('event', $event);
    $this->set('participants', $participants);
    $this->set('eventWorkshops', $eventWorkshops);
    return null;
  }

  /**
   * This action should be used only with ajax calls. It returns the following json structure:
   *
   * ```
   * {
   *   workshops: [
   *     {
   *       id: string,
   *       name: string,
   *       description: string,
   *       available: 0|1,
   *       waiting: number
   *     },
   *     ...
   *   ]
   * }
   * ```
   *
   * @param string $id Id of participant, workshops the participating is attending are skipped.
   *
   * @return null
   */
  public function workshops(string $id): null
  {
    $event = Tables::events()->getNextEvent();
    $eventWorkshops = $event != null
      ? Tables::eventWorkshops()->getAllForEvent($event)
      : [];
    $workshopItems = [];
    foreach ($eventWorkshops as $eventWorkshop) {
      if ($eventWorkshop->isParticipating($id)) {
        continue;
      }
      $participantCount = Tables::participants()->getCountForWorkshop($eventWorkshop);
      $workshopItems[] = [
        'id' => $eventWorkshop->id,
        'name' => $eventWorkshop->getName(),
        'description' => $eventWorkshop->getDescription(),
        'available' => $eventWorkshop->place_count > $participantCount ? 1 : 0,
        'waiting' => max(0, $participantCount - $eventWorkshop->place_count),
      ];
    }
    usort($workshopItems, fn($a, $b) => $a['name'] <=> $b['name']);
    $this->set('root', ['workshops' => $workshopItems]);
    $this->viewBuilder()
      ->setClassName(JsonView::class)
      ->setOption('serialize', 'root');
    return null;
  }

  /**
   * Removes a participant from the user's list of participants.
   *
   * @return Response|null
   */
  public function removeParticipant(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $event = Tables::events()->getNextEvent();
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $participant = Tables::participants()->getForId($viewData->id);
    // make sure the participant belongs to the user
    if ($participant->user_id != $this->user()->id) {
      return $this->redirectWithError(self::INDEX, __('Participant not found.'));
    }
    ParticipantTool::deleteParticipant($participant);
    return $this->redirect(self::INDEX);
  }

  /**
   * Selects a workshop for a participant.
   *
   * @return Response|null
   */
  public function selectWorkshop(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new SelectWorkshopViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $participant = Tables::participants()->getForId($viewData->participant_id);
    // make sure the participant belongs to the user
    if ($participant->user_id != $this->user()->id) {
      return $this->redirectWithError(self::INDEX, __('Participant not found.'));
    }
    $event = Tables::events()->getForId($participant->event_id);
    $eventWorkshop = Tables::eventWorkshops()->getForId($viewData->workshop_id);
    switch ($viewData->index) {
      case WorkshopIndex::BACKUP:
        ParticipantTool::joinBackupWorkshop($this->user(), $participant, $event, $eventWorkshop);
        break;
      default:
        ParticipantTool::joinFirstWorkshop($this->user(), $participant, $event, $eventWorkshop);
        break;
    }
    if (Tables::participants()->save($participant)) {
      return $this->redirect(self::INDEX);
    }
    return $this->redirectWithError(self::INDEX, __('Failed to save workshop selection.'));
  }

  /**
   * Removes a workshop from a participant.
   *
   * @return Response|null
   */
  public function removeWorkshop(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new RemoveWorkshopViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $participant = Tables::participants()->getForId($viewData->participant_id);
    // make sure the participant belongs to the user
    if ($participant->user_id != $this->user()->id) {
      return $this->redirectWithError(self::INDEX, __('Participant not found.'));
    }
    $result = match ($viewData->index) {
      WorkshopIndex::BACKUP => ParticipantTool::leaveBackupWorkshop($this->user(), $participant),
      default => ParticipantTool::leaveFirstWorkshop($this->user(), $participant),
    };
    if ($result) {
      return $this->redirect(self::INDEX);
    }
    return $this->redirectWithError(self::INDEX, __('Failed to remove workshop.'));
  }

  /**
   * This action is ment for links inside emails. It shows a confirmation page to remove a
   * participant from a workshop.
   *
   * @param string $eventWorkshopId
   * @param string $participantId
   *
   * @return Response|null
   */
  public function removeFromWorkshop(string $eventWorkshopId, string $participantId): ?Response
  {
    try {
      $participant = Tables::participants()->getForId($participantId);
      if (
        ($participant->event_workshop_1_id !== $eventWorkshopId) &&
        ($participant->event_workshop_2_id !== $eventWorkshopId)
      ) {
        return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
      }
      $eventWorkshop = Tables::eventWorkshops()->getForId($eventWorkshopId);
      $event = Tables::events()->getForId($participant->event_id);
      if (!$event->hasActiveSignup()) {
        return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
      }
      $this->set('participant', $participant);
      $this->set('eventWorkshop', $eventWorkshop);
      $this->set('event', $event);
    }
    catch (Exception $exception) {
      return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
    }
    return null;
  }

  /**
   * Processes the confirm form.
   *
   * @return Response|null
   */
  public function confirmRemoveFromWorkshop(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
    }
    $viewData = new RemoveFromWorkshopViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
    }
    try {
      $participant = Tables::participants()->getForId($viewData->participant_id);
      $event = Tables::events()->getForId($participant->event_id);
      // check again, to catch worst case if visitor left open the browser window for a few days
      if (!$event->hasActiveSignup()) {
        return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
      }
      $user = Tables::users()->getForId($participant->user_id);
      $result = false;
      if ($participant->event_workshop_1_id == $viewData->event_workshop_id) {
        $result = ParticipantTool::leaveFirstWorkshop($user, $participant);
      }
      elseif ($participant->event_workshop_2_id == $viewData->event_workshop_id) {
        $result = ParticipantTool::leaveBackupWorkshop($user, $participant);
      }
      if (!$result) {
        return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
      }
      return $this->redirect(self::REMOVE_FROM_WORKSHOP_SUCCESS);
    }
    catch (Exception $exception) {
      return $this->redirect(self::REMOVE_FROM_WORKSHOP_ERROR);
    }
  }

  /**
   * This page is shown if an error occurred while removing a participant from a workshop.
   *
   * @return Response|null
   */
  public function removeFromWorkshopError(): ?Response
  {
    return null;
  }

  /**
   * This page is shown after a participant has been removed from a workshop.
   *
   * @return Response|null
   */
  public function removeFromWorkshopSuccess(): ?Response
  {
    return null;
  }

  #endregion

  #region protected methods

  /**
   * @inheritdoc
   */
  protected function getAnonymousActions(): array
  {
    return [
      'removeFromWorkshop',
      'confirmRemoveFromWorkshop',
      'removeFromWorkshopError',
      'removeFromWorkshopSuccess'
    ];
  }

  #endregion

  #region private variables

  /**
   * Processes the add participant form.
   *
   * @param EventEntity $event
   *
   * @return EditParticipantViewModel
   */
  private function processAddParticipant(EventEntity $event): EditParticipantViewModel
  {
    $viewData = new EditParticipantViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_ADD_PARTICIPANT) != null) &&
      $viewData->patch($this->getRequest()->getData())
    ) {
      /** @var ParticipantEntity $participant */
      $participant = Tables::participants()->newEmptyEntity();
      $viewData->copyToEntity($participant);
      $participant->user_id = $this->user()->id;
      $participant->event_id = $event->id;
      if (Tables::participants()->save($participant)) {
        $this->redirect(self::INDEX);
        $viewData->clear();
      }
      else {
        $this->error(__('Participant could not be saved.'));
      }
    }
    return $viewData;
  }

  /**
   * Processes the edit participant form.
   *
   * @param EventEntity $event
   *
   * @return EditParticipantViewModel
   */
  private function processEditParticipant(EventEntity $event): EditParticipantViewModel
  {
    $viewData = new EditParticipantViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_EDIT_PARTICIPANT) != null) &&
      $viewData->patch($this->getRequest()->getData())
    ) {
      $participant = Tables::participants()->getForid($viewData->id);
      if (
        ($participant->user_id == $this->user()->id) &&
        ($participant->event_id == $event->id)
      ) {
        $viewData->copyToEntity($participant);
        if (Tables::participants()->save($participant)) {
          $this->redirect(self::INDEX);
          $viewData->clear();
        }
        else {
          $this->error(__('Participant could not be saved.'));
        }
      }
      else {
        $this->error(__('Participant not found.'));
      }
    }
    return $viewData;
  }

  #endregion
}
