<?php

namespace App\Model\View\EventWorkshops;

use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\WorkshopEntity;
use App\Model\Tables;
use App\Model\View\IdViewModel;
use Cake\Validation\Validator;
use Exception;

/**
 * A view model for editing an event workshop.
 */
class EditEventWorkshopViewModel extends IdViewModel
{
  #region field constants

  public const PLACE_COUNT = 'place_count';
  public const WORKSHOP_ID = 'workshop_id';

  #endregion

  #region public properties

  public int $place_count = 0;

  public string $workshop_id = '';

  /**
   * Helper property, will be set by the controller when editing an existing event workshop.
   *
   * @var WorkshopEntity|null
   */
  public ?WorkshopEntity $workshop = null;

  #endregion

  #region constructor

  public function __construct()
  {
    parent::__construct();
    $this->clear();
  }

  #endregion

  #region public methods

  /**
   * Copies the data from the view model to the entity.
   *
   * @throws Exception in case there is mismatch between ids of an existing entity and the view
   * model
   */
  public function copyToEntity(EventWorkshopEntity $entity, string $eventId): void
  {
    $entity->place_count = $this->place_count;
    if ($this->isNew()) {
      $entity->workshop_id = $this->workshop_id;
      $entity->event_id = $eventId;
    }
    else {
      if (($entity->workshop_id != $this->workshop_id) || ($entity->event_id != $eventId)) {
        throw new Exception('Cannot change the workshop or event of an existing workshop.');
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function clear(): void
  {
    parent::clear();
    $this->place_count = 0;
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::PLACE_COUNT => __('place count'),
      self::WORKSHOP_ID => __('workshop'),
      default => parent::getFieldName($field),
    };
  }

  #endregion

  #region protected methods

  /**
   * @inheritDoc
   */
  protected function buildValidator(): Validator
  {
    return parent::buildValidator()
      ->greaterThan(self::PLACE_COUNT, 0, __('The number of places must be greater than 0.'))
      ->add(self::PLACE_COUNT, 'canDecrease', [
        'rule' => fn($value, array $context) => $this->canDecrease($value, $context)
      ]);
  }

  #endregion

  #region private methods

  /**
   * Checks if the new places can be decreased (not while the signup is active).
   *
   * @param string $value
   * @param array $context
   *
   * @return bool|string
   */
  private function canDecrease(string $value, array $context): bool|string
  {
    $data = $context['data'];
    if (empty($data[self::ID])) {
      return true;
    }
    $newPlaces = $data[self::PLACE_COUNT];
    $eventWorkshop = Tables::eventWorkshops()->getForIdWithEvent($data[self::ID]);
    if (
      ($newPlaces >= $eventWorkshop->place_count) ||
      !$eventWorkshop->event->hasActiveSignup()
    ) {
      return true;
    }
    return __('The number of places can not be decreased while the signup is active.');
  }

  #endregion
}
