<?php

namespace App\Model\View;

use App\Lib\Model\View\ViewModelBase;
use App\Model\Entity\UserEntity;
use Cake\Validation\Validator;

/**
 * A view model to handle the editing of a user profile.
 */
class EditProfileViewModel extends ViewModelBase
{
  #region property names

  public const NAME = 'name';
  public const PHONE = 'phone';

  #endregion

  #region properties

  /**
   * Users name
   *
   * @var string
   */
  public string $name = '';

  /**
   * Users phone
   *
   * @var string
   */
  public string $phone = '';

  #endregion

  #region public methods

  /**
   * @inheritDoc
   */
  public function clear(): void {
  }

  /**
   * Copies the data from the entity to the view model.
   *
   * @param UserEntity $user
   *
   * @return void
   */
  public function copyFromEntity(UserEntity $user): void {
    $this->name = $user->name;
    $this->phone = $user->phone;
  }

  /**
   * Copies the data from the view model to the entity.
   *
   * @param UserEntity $user
   *
   * @return void
   */
  public function copyToEntity(UserEntity $user): void {
    $user->name = $this->name;
    $user->phone = $this->phone;
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::NAME => __('name'),
      self::PHONE => __('phone'),
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
      ->notEmptyString(self::NAME, __('The name can not be empty.'));
  }

  #endregion
}
