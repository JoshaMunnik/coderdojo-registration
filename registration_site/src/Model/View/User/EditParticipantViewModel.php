<?php

namespace App\Model\View\User;

use App\Model\Entity\ParticipantEntity;
use App\Model\View\IdViewModel;

/**
 * {@link EditParticipantViewModel} is the view model for editing a participant.
 */
class EditParticipantViewModel extends IdViewModel
{
  #region field constant

  public const NAME = 'name';
  public const HAS_LAPTOP = 'has_laptop';
  public const CAN_LEAVE = 'can_leave';

  #endregion

  #region fields

  public string $name = '';

  public bool $has_laptop = false;

  public bool $can_leave = false;

  #endregion

  #region public methods

  /**
   * Copies the data from the view model to the entity.
   *
   * @param ParticipantEntity $entity
   *
   * @return void
   */
  public function copyToEntity(ParticipantEntity $entity): void
  {
    $entity->name = $this->name;
    $entity->has_laptop = $this->has_laptop;
    $entity->can_leave = $this->can_leave;
  }

  /**
   * @inheritdoc
   */
  public function clear(): void
  {
    parent::clear();
    $this->name = '';
    $this->has_laptop = false;
    $this->can_leave = false;
  }

  #endregion
}
