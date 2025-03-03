<?php

namespace App\Model\View\Account;

use App\Lib\Model\View\ViewModelBase;
use App\Model\Tables;
use Cake\Validation\Validator;

/**
 * A view model to handle the changing of a password.
 */
class RegistrationViewModel extends ViewModelBase
{
  #region property names

  public const EMAIL = 'email';
  public const PASSWORD = 'password';
  public const CONFIRM_PASSWORD = 'confirm_password';
  public const NAME = 'name';
  public const PHONE = 'phone';
  public const MAILING_LIST = 'mailing_list';
  public const AGREE_TERMS = 'agree_terms';

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
   * @var string
   */
  public string $confirm_password = '';

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

  /**
   * Receive mailings
   *
   * @var bool
   */
  public bool $mailing_list = false;

  /**
   * Agree to terms
   *
   * @var bool
   */
  public bool $agree_terms = false;

  #endregion

  #region public methods

  public function clear(): void
  {
    $this->password = '';
    $this->confirm_password = '';
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::EMAIL => __('email'),
      self::PASSWORD => __('password'),
      self::CONFIRM_PASSWORD => __('confirm password'),
      self::NAME => __('name'),
      self::PHONE => __('phone'),
      self::MAILING_LIST => __('mailing list'),
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
      ->notEmptyString(self::NAME, __('The name can not be empty.'))
      ->notEmptyString(self::PASSWORD, __('The password can not be empty.'))
      ->notEmptyString(self::CONFIRM_PASSWORD, __('The confirm password can not be empty.'))
      ->equalToField(
        self::PASSWORD, self::CONFIRM_PASSWORD, __('The new passwords are not equal.')
      )
      ->equals(self::AGREE_TERMS, true, __('You must agree to the terms.'))
      ->add(self::EMAIL, 'unusedEmail', [
        'rule' => fn($value, array $context) => $this->isUnusedEmail($value, $context)
      ]);
  }

  #endregion

  #region private methods

  /**
   * Check if the event id is valid for the chosen user role.
   *
   * @param string $aValue
   * @param array $aContext
   *
   * @return bool|string
   */
  private function isUnusedEmail(string $aValue, array $aContext): bool|string
  {
    $email = $aContext['data'][self::EMAIL];
    $users = Tables::users();
    if ($users->isUnusedEmail($email)) {
      return true;
    }
    return __('There is already a registration for the email address.');
  }

  #endregion
}
