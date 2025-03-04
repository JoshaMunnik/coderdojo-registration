<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\WorkshopEntity;
use App\Model\Tables;
use App\Model\View\EventWorkshops\EditEventWorkshopViewModel;
use App\Model\View\IdViewModel;
use App\Tool\FileTool;
use App\Tool\ParticipantTool;
use Cake\Http\Response;
use Exception;

/**
 * {@link EventWorkshopsController} manages the workshops for an event.
 */
class EventWorkshopsController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const REMOVE = [self::class, 'remove'];
  public const DOWNLOAD = [self::class, 'download'];

  public const SUBMIT_ADD = 'submit_add';
  public const SUBMIT_EDIT = 'submit_edit';

  #endregion

  #region public methods

  /**
   * Shows all workshops for an event and processes various forms.
   *
   * @param string $id Event id
   *
   * @return Response|null
   *
   * @throws Exception
   */
  public function index(string $id): ?Response
  {
    $event = Tables::events()->getForId($id);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($event);
    $workshops = $this->getEligibleWorkshops($eventWorkshops);
    $this->set('event', $event);
    $this->set('eventWorkshops', $eventWorkshops);
    $this->set('workshops', $workshops);
    $this->set('editEventWorkshopData', $this->processEdit($event));
    $this->set('addEventWorkshopData', $this->processAdd($event));
    return null;
  }

  /**
   * Handles the removing of a workshop from an event.
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
    $eventWorkshop = Tables::eventWorkshops()->getForId($viewData->id);
    Tables::eventWorkshops()->delete($eventWorkshop);
    return $this->redirectWithSuccess(
      [self::INDEX, $eventWorkshop->event_id],
      __('The workshop has been removed.')
    );
  }

  /**
   * Downloads a CSV file with the workshops for an event.
   *
   * @param $id
   *
   * @return Response
   */
  public function download($id): Response {
    $event = Tables::events()->getForId($id);
    $eventWorkshops = Tables::eventWorkshops()->getAllForEvent($event);
    $headers = [
      __('Workshop'),
      __('Places'),
      __('Participants'),
      __('Waiting'),
      __('Laptops needed'),
    ];
    $data = [];
    foreach($eventWorkshops as $eventWorkshop) {
      $participantCount = Tables::participants()->getCountForWorkshop($eventWorkshop);
      $data[] = [
        $eventWorkshop->getName(),
        $eventWorkshop->place_count,
        min($participantCount, $eventWorkshop->place_count),
        max(0, $participantCount - $eventWorkshop->place_count),
        $eventWorkshop->getLaptopsNeededCount(),
      ];
    }
    $filename = FileTool::addDate('workshops.csv', $event->event_date->toNative());
    return $this->exportCsv($filename, $data, $headers);
  }

  #endregion

  #region private methods

  /**
   * Checks if the workshop has not been added yet.
   *
   * @param WorkshopEntity $workshop
   * @param EventWorkshopEntity[] $eventWorkshops
   *
   * @return bool True if the workshop has not been added yet.
   */
  private function isWorkshopAvailable(WorkshopEntity $workshop, array $eventWorkshops): bool
  {
    foreach ($eventWorkshops as $eventWorkshop) {
      if ($eventWorkshop->workshop_id === $workshop->id) {
        return false;
      }
    }
    return true;
  }

  /**
   * Processes the edit form.
   *
   * @param EventEntity $event
   *
   * @return EditEventWorkshopViewModel
   *
   * @throws Exception
   */
  private function processEdit(
    EventEntity $event
  ): EditEventWorkshopViewModel {
    $eventWorkshopData = new EditEventWorkshopViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_EDIT) != null) &&
      $eventWorkshopData->patch($this->getRequest()->getData())
    ) {
      $eventWorkshop = Tables::eventWorkshops()->getForId(
        $eventWorkshopData->id
      );
      $eventWorkshopData->copyToEntity($eventWorkshop, $event->id);
      if (Tables::eventWorkshops()->save($eventWorkshop)) {
        if ($event->hasActiveSignup()) {
          ParticipantTool::checkParticipatingStatusForEvent($event);
        }
        $this->redirectWithSuccess(
          [self::INDEX, $event->id],
          __('The workshop {0} has been updated.', $eventWorkshop->getName())
        );
        $eventWorkshopData->clear();
      }
      else {
        $this->error(__('The workshop could not be saved in the database.'));
      }
    }
    return $eventWorkshopData;
  }

  /**
   * Process the add form.
   *
   * @param EventEntity $event
   *
   * @return EditEventWorkshopViewModel
   *
   * @throws Exception
   */
  private function processAdd(
    EventEntity $event
  ): EditEventWorkshopViewModel {
    $eventWorkshopData = new EditEventWorkshopViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_ADD) != null) &&
      $eventWorkshopData->patch($this->getRequest()->getData())
    ) {
      /** @var EventWorkshopEntity $eventWorkshop */
      $eventWorkshop = Tables::eventWorkshops()->newEmptyEntity();
      $eventWorkshopData->copyToEntity($eventWorkshop, $event->id);
      if (Tables::eventWorkshops()->save($eventWorkshop)) {
        $workshop = Tables::workshops()->getForId($eventWorkshop->workshop_id);
        $name = $workshop->getName();
        $date = $event->getEventDateAsText();
        $this->redirectWithSuccess(
          [self::INDEX, $event->id],
          __('The workshop {0} has been added to the event for {1}.', $name, $date)
        );
        $eventWorkshopData->clear();
      }
      else {
        $this->error(__('The workshop could not be saved in the database.'));
      }
    }
    return $eventWorkshopData;
  }

  /**
   * @param EventWorkshopEntity[] $eventWorkshops
   *
   * @return WorkshopEntity[]
   */
  private function getEligibleWorkshops(array $eventWorkshops): array
  {
    $workshops = Tables::workshops()->getAll();
    return array_filter($workshops,
      fn(WorkshopEntity $workshop) => $this->isWorkshopAvailable(
        $workshop, $eventWorkshops
      )
    );
  }

  #endregion
}
