<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\EventEntity;
use App\Model\Tables;
use App\Model\View\Events\EditEventViewModel;
use App\Model\View\IdViewModel;
use Cake\Http\Response;

/**
 * {@link EventsController} manages the events.
 */
class EventsController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const REMOVE = [self::class, 'remove'];
  public const ANONYMIZE = [self::class, 'anonymize'];

  public const SUBMIT_ADD = 'submit_add';
  public const SUBMIT_EDIT = 'submit_edit';

  #endregion

  #region public methods

  /**
   * Shows the home page and processes forms.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $this->set('editEventData', $this->processEdit());
    $this->set('addEventData', $this->processAdd());
    $this->set('events', Tables::events()->getAllWithCounts());
    return null;
  }

  /**
   * Handles the removing of an event.
   *
   * @return Response|null
   */
  public function remove(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(AdministratorController::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(AdministratorController::INDEX);
    }
    $event = Tables::events()->getForId($viewData->id);
    Tables::events()->delete($event);
    $eventDate = $event->event_date->format('Y-m-d');
    return $this->redirectWithSuccess(
      self::INDEX,
      __('The event for {0} has been removed.', $eventDate)
    );
  }

  /**
   * Handles the anonymizing of  the event.
   *
   * @return Response|null
   */
  public function anonymize(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(AdministratorController::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(AdministratorController::INDEX);
    }
    $event = Tables::events()->getForId($viewData->id);
    Tables::events()->addAbsentParticipants($event);
    Tables::events()->anonymizeParticipants($event);
    $eventDate = $event->event_date->format('Y-m-d');
    return $this->redirectWithSuccess(
      self::INDEX,
      __('The event for {0} has been anonymized.', $eventDate)
    );
  }

  #endregion

  #region private methods

  /**
   * Processes the edit form.
   *
   * @return EditEventViewModel
   */
  private function processEdit(): EditEventViewModel
  {
    $eventData = new EditEventViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_EDIT) != null) &&
      $eventData->patch($this->getRequest()->getData())
    ) {
      $event = Tables::events()->getForId($eventData->id);
      $eventData->copyToEntity($event);
      if (Tables::events()->save($event)) {
        $eventDate = $event->event_date->format('Y-m-d H:m');
        $this->redirectWithSuccess(
          self::INDEX,
          __('The event for {0} has been updated.', $eventDate)
        );
      }
      else {
        $this->error(__('The event could not be saved to the database.'));
      }
    }
    return $eventData;
  }

  /**
   * Process the add form.
   *
   * @return EditEventViewModel
   */
  private function processAdd(): EditEventViewModel
  {
    $eventData = new EditEventViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_ADD) != null) &&
      $eventData->patch($this->getRequest()->getData())
    ) {
      /** @var EventEntity $event */
      $event = Tables::events()->newEmptyEntity();
      $eventData->copyToEntity($event);
      if (Tables::events()->save($event)) {
        $eventDate = $event->event_date->format('Y-m-d H:m');
        $this->redirectWithSuccess(
          self::INDEX,
          __('The event for {0} has been added.', $eventDate)
        );
        $eventData->clear();
      }
      else {
        $this->error(__('The event could not be saved to the database.'));
      }
    }
    return $eventData;
  }
}
