<?php

namespace App\Controller;

use App\Lib\Controller\ApplicationControllerBase;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Model\View\Account\ForgotPasswordViewModel;
use App\Model\View\Account\LoginViewModel;
use App\Model\View\Account\RegistrationViewModel;
use App\Model\View\Account\ResetPasswordViewModel;
use App\Service\EmailService;
use Cake\Http\Response;
use Random\RandomException;

/**
 * {@link AccountController} handles the login, logout and account related actions.
 */
class AccountController extends ApplicationControllerBase
{
  #region public constants

  public const LOGIN = [self::class, 'login'];
  public const LOGOUT = [self::class, 'logout'];
  public const RESET_PASSWORD = [self::class, 'reset-password'];
  public const RESET_PASSWORD_ERROR = [self::class, 'reset-password-error'];

  public const SUBMIT_FORGOT_PASSWORD = 'submit_forgot_password';
  public const SUBMIT_REGISTER = 'submit_register';

  #endregion

  #region public methods

  /**
   * Processes any form posted in the login page.
   *
   * @return Response|null
   *
   * @throws RandomException
   */
  public function login(): ?Response
  {
    if ($this->isSubmit()) {
      if ($this->getRequest()->getData(self::SUBMIT_FORGOT_PASSWORD) != null) {
        return $this->processForgotPasswordForm();
      }
      else {
        if ($this->getRequest()->getData(self::SUBMIT_REGISTER) != null) {
          return $this->processRegistrationForm();
        }
      }
    }
    return $this->processLogin();
  }

  /**
   * Log out the current user.
   *
   * @return Response|null
   */
  public function logout(): ?Response
  {
    $this->Authentication->logout();
    return $this->redirect(self::LOGIN);
  }

  /**
   * Change the password of the current user.
   *
   * @param string $code Code send to the user.
   *
   * @return Response|null
   */
  public function resetPassword(string $code): ?Response
  {
    $resetPasswordData = new ResetPasswordViewModel();
    if ($this->isSubmit()) {
      $result = $this->processResetPasswordForm($resetPasswordData);
      if (isset($result)) {
        return $result;
      }
    }
    else {
      if (Tables::users()->validateResetToken($code)) {
        $resetPasswordData->reset_token = $code;
      }
      else {
        return $this->redirect(self::RESET_PASSWORD_ERROR);
      }
    }
    // clear all password fields
    $this->withoutData(
      ResetPasswordViewModel::NEW_PASSWORD,
      ResetPasswordViewModel::CONFIRM_PASSWORD
    );
    $resetPasswordData->clear();
    $this->set('resetPasswordData', $resetPasswordData);
    return null;
  }

  /**
   * Shows an error message when the reset password failed.
   *
   * @return void
   */
  public function resetPasswordError()
  {
  }

  #endregion

  #region protected methods

  /**
   * @inheritdoc
   */
  protected function getAnonymousActions(): array
  {
    return [
      'login',
      'register',
      'forgotPassword',
      'resetPassword',
      'resetPasswordError',
    ];
  }

  #endregion

  #region private methods

  /**
   * Processes the registration form
   *
   * @return Response|null
   */
  private function processRegistrationForm(): ?Response
  {
    $registrationData = new RegistrationViewModel();
    if ($registrationData->patch($this->getRequest()->getData())) {
      $user = new UserEntity();
      $user->copyFromRegistration($registrationData);
      if (tables::users()->save($user) !== false) {
        return $this->redirectWithSuccess(
          self::LOGIN,
          __('Your account has been created. Please login.')
        );
      }
      $this->error(__('An error occurred with the database, your account has not been created.'));
    }
    $registrationData->clear();
    $this->set('registrationData', $registrationData);
    return null;
  }

  /**
   * Processes the forgot password form.
   *
   * @throws RandomException
   */
  private function processForgotPasswordForm(): ?Response
  {
    $forgotPasswordData = new ForgotPasswordViewModel();
    if ($forgotPasswordData->patch($this->getRequest()->getData())) {
      $user = tables::users()->findForEmail($forgotPasswordData->email);
      // if user does not exist, act like an email is sent so that attackers cannot
      // determine valid email addresses.
      if ($user) {
        $user->generateResetToken();
        if (tables::users()->save($user)) {
          EmailService::sendResetEmailToken($user, $user->password_reset_token);
        }
        else {
          return $this->redirectWithError(
            self::LOGIN,
            __('An error occurred with the database, your reset token has not been created.')
          );
        }
      }
    }
    return $this->redirectWithSuccess(
      self::LOGIN,
      __('An email has been sent to you with instructions to reset your password.')
    );
  }

  /**
   * Processes the reset password form.
   *
   * @param ResetPasswordViewModel $form
   *
   * @return Response|null
   */
  private function processResetPasswordForm(ResetPasswordViewModel $form): ?Response
  {
    if ($form->patch($this->getRequest()->getData())) {
      $user = tables::users()->findUserForResetToken($form->reset_token);
      if ($user) {
        $user->assignNewPassword($form->new_password);
        if (tables::users()->save($user)) {
          return $this->redirectWithSuccess(
            self::LOGIN,
            __('Your password has been reset. Please login.')
          );
        }
        $this->error(__('An error occurred with the database, your password has not been reset.'));
      }
      else {
        return $this->redirect(self::RESET_PASSWORD_ERROR);
      }
    }
    return null;
  }

  /**
   * Processes the login form or cookie or session.
   *
   * @return Response|null
   */
  private function processLogin(): ?Response
  {
    // process form or cookie or session
    $result = $this->Authentication->getResult();
    if ($result->isValid()) {
      return $this->redirect($this->Authentication->getLoginRedirect() ?? $this->getHomeAction());
    }
    // failure, show error if a form was submitted
    if ($this->isSubmit()) {
      $this->error(__('Invalid or unknown credentials.'));
    }
    $loginData = new LoginViewModel();
    $loginData->patch($this->getRequest()->getData());
    $loginData->clear();
    $this->set('loginData', $loginData);
    return null;
  }

  #endregion
}
