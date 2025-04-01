<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use App\Model\Value\Language;
use App\Model\View\Account\RegistrationViewModel;
use App\Model\View\EditProfileViewModel;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use DateTime;
use Random\RandomException;

/**
 * {@link UserEntity} encapsulates a user in the database.
 * *
 * @property string $email
 * @property string $name
 * @property string $password
 * @property DateTime $password_date
 * @property string|null $phone
 * @property bool $administrator
 * @property string|null $password_reset_token
 * @property DateTime|null $password_reset_date
 * @property DateTime $last_visit_date
 * @property bool $mailing_list
 * @property int $language_id
 * @property bool $disable_email
 * @property string $public_id
 */
class UserEntity extends Entity implements IEntityWithId, IEntityWithTimestamp
{
  #region field constants

  public const EMAIL = 'email';
  public const NAME = 'name';
  public const PASSWORD = 'password';
  public const PASSWORD_DATE = 'password_date';
  public const PHONE = 'phone';
  public const ADMINISTRATOR = 'administrator';
  public const PASSWORD_RESET_TOKEN = 'password_reset_token';
  public const PASSWORD_RESET_DATE = 'password_reset_date';
  public const LAST_VISIT_DATE = 'last_visit_date';
  public const MAILING_LIST = 'mailing_list';
  public const LANGUAGE = 'language_id';
  public const DISABLE_EMAIL = 'disable_email';
  public const PUBLIC_ID = 'public_id';

  #endregion

  #region private variables

  /**
   * Cached QRCode instance.
   */
  private QRCode $m_qrcode;

  #endregion

  #region public methods

  /**
   * Checks if the password matches the stored password.
   *
   * @param string $password Password to check
   *
   * @return bool True if the password matches
   */
  public function isCorrectPassword(string $password): bool
  {
    return (new DefaultPasswordHasher())->check($password, $this->password);
  }

  /**
   * Copies the fields from a registration view model.
   *
   * @param RegistrationViewModel $registration
   *
   * @return void
   */
  public function copyFromRegistration(RegistrationViewModel $registration): void
  {
    $this->email = $registration->email;
    $this->name = $registration->name;
    $this->password = $registration->password;
    $this->phone = $registration->phone;
    $this->language_id = Language::getId(I18n::getLocale());
    $this->mailing_list = $registration->mailing_list;
    $this->administrator = false;
    $this->disable_email = false;
  }

  /**
   * Copies the fields from a edit profile view model.
   *
   * @param EditProfileViewModel $editProfile
   *
   * @return void
   */
  public function copyFromEditProfile(EditProfileViewModel $editProfile): void
  {
    $this->name = $editProfile->name;
    $this->phone = $editProfile->phone;
  }

  /**
   * Assigns a new password and also update {@link $password_date}, {@link $password_reset_date},
   * and {@link $password_reset_token}.
   *
   * @param $password
   *
   * @return void
   */
  public function assignNewPassword($password): void
  {
    $this->password_date = new DateTime();
    $this->password = (new DefaultPasswordHasher())->hash($password);
    $this->password_reset_date = null;
    $this->password_reset_token = null;
  }

  /**
   * Generates a new password reset token and sets the {@link $password_reset_date}.
   *
   * @return void
   *
   * @throws RandomException
   */
  public function generateResetToken(): void
  {
    $this->password_reset_token = bin2hex(random_bytes(16));
    $this->password_reset_date = new DateTime();
  }

  /**
   * Gets a base64 encoded QR code image for the user.
   *
   * @return string
   */
  public function getQRCodeImage(): string
  {
    if (!isset($this->m_qrcode)) {
      $options = new QROptions([
        'version'    => 2,
        'outputType' => QROutputInterface::GDIMAGE_PNG, //QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => EccLevel::H,
      ]);
      $this->m_qrcode = new QRCode($options);
    }
    return $this->m_qrcode->render($this->public_id);
  }


  #endregion

  #region protected methods

  /**
   * Stores a hashed version of the password, if it is not empty.
   */
  protected function _setPassword(string $password): string
  {
    if (strlen($password) > 0) {
      $this->password_date = new DateTime();
      return (new DefaultPasswordHasher())->hash($password);
    }
    return '';
  }

  /**
   * Stores the email in lowercase.
   */
  protected function _setEmail(string $email): string
  {
    return strtolower($email);
  }

  #endregion
}
