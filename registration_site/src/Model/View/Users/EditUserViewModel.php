<?php

namespace App\Model\View\Users;

use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Model\Value\Language;
use App\Model\View\IdViewModel;
use Cake\Validation\Validator;
use DateTime;

/**
 * {@link EditUserViewModel} is the view model for editing a user.
 */
class EditUserViewModel extends IdViewModel
{
  #region field constants

  const EMAIL = 'email';
  const NAME = 'name';
  const PASSWORD = 'password';
  const PHONE = 'phone';
  const ADMINISTRATOR = 'administrator';
  const MAILING_LIST = 'mailing_list';
  const LANGUAGE = 'language_id';
  const DISABLE_EMAIL = 'disable_email';

  #endregion

  #region public properties

  public string $email = '';
  public string $name = '';
  public string $password = '';
  public string $phone = '';
  public bool $administrator = false;
  public bool $mailing_list = false;
  public int $language_id = Language::DUTCH_ID;
  public bool $disable_email = false;

  #endregion

  #region public methods

  public function copyFromEntity(UserEntity $entity): void
  {
    $this->id = $entity->id;
    $this->email = $entity->email;
    $this->name = $entity->name;
    $this->phone = $entity->phone;
    $this->administrator = $entity->administrator;
    $this->mailing_list = $entity->mailing_list;
    $this->language_id = $entity->language_id;
    $this->disable_email = $entity->disable_email;
    $this->password = '';
    $this->setNew(false);
  }

  public function copyToEntity(UserEntity $entity): void
  {
    $entity->email = $this->email;
    $entity->name = $this->name;
    $entity->phone = $this->phone;
    $entity->administrator = $this->administrator;
    $entity->mailing_list = $this->mailing_list;
    $entity->language_id = $this->language_id;
    $entity->disable_email = $this->disable_email;
    if (!empty($this->password)) {
      $entity->password = $this->password;
    }
  }

  /**
   * @inheritDoc
   */
  public function clear(): void
  {
    parent::clear();
    $this->email = '';
    $this->name = '';
    $this->password = '';
    $this->phone = '';
    $this->administrator = false;
    $this->mailing_list = false;
    $this->disable_email = false;
    $this->language_id = Language::DUTCH_ID;
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::EMAIL => __('email'),
      self::NAME => __('name'),
      self::PASSWORD => __('password'),
      self::PHONE => __('phone'),
      self::ADMINISTRATOR => __('administrator'),
      self::MAILING_LIST => __('mailing list'),
      self::DISABLE_EMAIL => __('disable emails'),
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
      ->notEmptyString(UserEntity::NAME)
      ->notEmptyString(UserEntity::EMAIL)
      ->email(UserEntity::EMAIL)
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
    $id = $aContext['data'][self::ID];
    $users = Tables::users();
    if ($users->isUnusedEmail($email, $id)) {
      return true;
    }
    return __('There is already a user for the email address.');
  }

  #endregion
}
