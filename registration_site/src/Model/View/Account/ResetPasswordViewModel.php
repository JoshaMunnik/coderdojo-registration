<?php

namespace App\Model\View\Account;

use App\Lib\Model\View\ViewModelBase;
use Cake\Validation\Validator;

/**
 * A view model to handle the resetting of a password.
 */
class ResetPasswordViewModel extends ViewModelBase
{
  #region property names

  public const RESET_TOKEN = 'reset_token';
  public const NEW_PASSWORD = 'new_password';
  public const CONFIRM_PASSWORD = 'confirm_password';

  #endregion

  #region properties

  /**
   * Reset token.
   *
   * @var string
   */
  public string $reset_token = '';

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
    $this->new_password = '';
    $this->confirm_password = '';
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::NEW_PASSWORD => __('new password'),
      self::CONFIRM_PASSWORD => __('confirm password'),
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
      ->equalToField(
        self::NEW_PASSWORD, self::CONFIRM_PASSWORD, __('The new passwords are not equal.')
      );
  }

  #endregion
}
