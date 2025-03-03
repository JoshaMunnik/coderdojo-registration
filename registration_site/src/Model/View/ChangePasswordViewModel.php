<?php

namespace App\Model\View;

use App\Lib\Model\View\ViewModelBase;
use Cake\Validation\Validator;

/**
 * A view model to handle the changing of a password.
 */
class ChangePasswordViewModel extends ViewModelBase
{
  #region property names

  public const CURRENT_PASSWORD = 'current_password';
  public const NEW_PASSWORD = 'new_password';
  public const CONFIRM_PASSWORD = 'confirm_password';

  #endregion

  #region properties

  /**
   * Current password
   *
   * @var string
   */
  public string $current_password = '';

  /**
   * New password
   *
   * @var string
   */
  public string $new_password = '';

  /**
   * Confirm password
   *
   * @var string
   */
  public string $confirm_password = '';

  #endregion

  #region public methods

  /**
   * @inheritDoc
   */
  public function clear(): void {
    $this->current_password = '';
    $this->new_password = '';
    $this->confirm_password = '';
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::CURRENT_PASSWORD => __('current password'),
      self::NEW_PASSWORD => __('new password'),
      self::CONFIRM_PASSWORD => __('confirm new password'),
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
      ->notEmptyString(self::NEW_PASSWORD, __('The new password can not be empty.'))
      ->notEmptyString(self::CONFIRM_PASSWORD, __('The confirm password can not be empty.'))
      ->notEmptyString(self::CURRENT_PASSWORD, __('The current password can not be empty.'))
      ->equalToField(
        self::NEW_PASSWORD, self::CONFIRM_PASSWORD, __('The new passwords are not equal.')
      );
  }

  #endregion
}
