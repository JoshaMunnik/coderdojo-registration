<?php

namespace App\Model\View\Account;

use App\Application;
use App\Lib\Model\View\ViewModelBase;
use Cake\Validation\Validator;

/**
 * The post fields are processed by the authentication plugin.
 *
 * See the implementation at {@link Application::getAuthenticationService()}.
 */
class LoginViewModel extends ViewModelBase
{
  #region fields

  const EMAIL = 'email';
  const PASSWORD = 'password';
  const REMEMBER_ME = 'remember_me';

  #endregion

  #region properties

  /**
   * Users email
   *
   * @var string
   */
  public string $email = '';

  /**
   * New password
   *
   * @var string
   */
  public string $password = '';

  /**
   * Confirm password
   *
   * @var bool
   */
  public bool $remember_me = false;

  #endregion

  #region public methods

  public function clear(): void {
    $this->password = '';
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::EMAIL => __('email'),
      self::PASSWORD => __('password'),
      self::REMEMBER_ME => __('remember me'),
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
      ->email(self::EMAIL, __('The email is not valid.'))
      ->notEmptyString(self::PASSWORD, __('The password can not be empty.'));
  }

  #endregion
}
