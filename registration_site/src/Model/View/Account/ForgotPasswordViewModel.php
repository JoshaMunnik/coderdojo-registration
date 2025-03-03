<?php

namespace App\Model\View\Account;

use App\Lib\Model\View\ViewModelBase;
use Cake\Validation\Validator;

/**
 * A view model to handle the request of a password reset.
 */
class ForgotPasswordViewModel extends ViewModelBase
{
  #region property names

  public const EMAIL = 'email';

  #endregion

  #region properties

  /**
   * Users email
   *
   * @var string
   */
  public string $email = '';

  #endregion

  #region public methods

  public function clear(): void {
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::EMAIL => __('email'),
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
      ->notEmptyString(self::EMAIL, __('The email can not be empty.'))
      ->email(self::EMAIL, __('The email is not valid.'));
  }

  #endregion
}
