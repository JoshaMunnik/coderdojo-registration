<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use App\Model\Entity\WorkshopEntity;
use App\Model\Tables;
use App\Model\View\IdViewModel;
use App\Model\View\Workshops\EditWorkshopViewModel;
use Cake\Http\Response;

/**
 * {@link WorkshopsController} manages the workshops.
 */
class WorkshopsController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const EDIT = [self::class, 'edit'];
  public const REMOVE = [self::class, 'remove'];

  #endregion

  #region public methods

  /**
   * Shows the list of workshops.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $this->set('workshops', Tables::workshops()->getAll());
    return null;
  }

  /**
   * Handles the editing/creating of a workshop.
   *
   * @param string|null $id
   * @return Response|null
   */
  public function edit(?string $id = null): ?Response
  {
    $viewData = $this->processEdit($id);
    $this->set('data', $viewData);
    return null;
  }

  /**
   * Handles the removal of a workshop.
   *
   * @return Response|null
   */
  public function remove(): ?Response
  {
    if (!$this->isSubmit()) {
      return $this->redirect(self::INDEX);
    }
    $viewData = new IdViewModel();
    if (!$viewData->patch($this->getRequest()->getData())) {
      return $this->redirect(self::INDEX);
    }
    $workshop = Tables::workshops()->getForId($viewData->id);
    Tables::workshops()->delete($workshop);
    return $this->redirectWithSuccess(
      self::INDEX,
      __('Workshop {0} has been removed.', $workshop->getName())
    );
  }

  #endregion

  #region private methods

  /**
   * Processes the edit/creation form.
   *
   * @param string|null $id
   *
   * @return EditWorkshopViewModel
   */
  private function processEdit(?string $id): EditWorkshopViewModel
  {
    $viewData = new EditWorkshopViewModel();
    if (!$this->isSubmit()) {
      if ($id) {
        $workshop = Tables::workshops()->getForId($id);
        $viewData->copyFromEntity($workshop);
      }
      return $viewData;
    }
    if ($viewData->patch($this->getRequest()->getData())) {
      /** @var WorkshopEntity $workshop */
      if ($viewData->isNew()) {
        $workshop = Tables::workshops()->newEmptyEntity();
      }
      else {
        $workshop = Tables::workshops()->getForId($viewData->id);
      }
      $viewData->copyToEntity($workshop);
      if (Tables::workshops()->save($workshop)) {
        $this->redirectWithSuccess(
          self::INDEX,
          $viewData->id
            ? __('Workshop {0} updated', $workshop->getName())
            : __('Workshop {0} created', $workshop->getName())
        );
      }
      else {
        $this->error(__('Failed to save workshop to the database'));
      }
    }
    return $viewData;
  }

  #endregion
}
