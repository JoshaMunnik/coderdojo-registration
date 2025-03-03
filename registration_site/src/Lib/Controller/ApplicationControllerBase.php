<?php
declare(strict_types=1);

namespace App\Lib\Controller;

use App\Controller\AccountController;
use App\Controller\AdministratorController;
use App\Controller\UserController;
use App\Model\Entity\UserEntity;
use App\Model\Tables;
use App\Model\Value\Language;
use App\Model\View\ChangePasswordViewModel;
use App\Model\View\EditProfileViewModel;
use App\Tool\UrlTool;
use App\View\ApplicationView;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Component\FlashComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use DateTime;
use Exception;
use Psr\Http\Message\UriInterface;

/**
 * Bases class for all application Controllers.
 *
 * @property AuthenticationComponent $Authentication
 * @property FlashComponent $Flash
 */
class ApplicationControllerBase extends Controller
{
  #region public constants

  const SUBMIT_EDIT_PROFILE = 'submit_edit_profile';

  const SUBMIT_CHANGE_PASSWORD = 'submit_change_password';

  #endregion

  #region private variables

  /**
   * Logged in user.
   *
   * @var null|UserEntity
   */
  private ?UserEntity $m_user = null;

  #endregion

  #region cakephp methods

  /**
   * Initialization hook method.
   *
   * Use this method to add common initialization code like loading components.
   *
   * e.g. `$this->loadComponent('FormProtection');`
   *
   * @return void
   *
   * @throws Exception If a component fails to load
   */
  public function initialize(): void
  {
    parent::initialize();
    $this->loadComponent('Flash');
    $this->viewBuilder()->setClassName(ApplicationView::class);
    if ($this->useAuthentication()) {
      $this->loadComponent('Authentication.Authentication');
      $this->Authentication->allowUnauthenticated($this->getAnonymousActions());
    }
    $user = $this->user();
    $this->processLanguage($user);
    if ($user != null) {
      $user->last_visit_date = new DateTime();
      Tables::users()->save($user);
    }

    /*
     * Enable the following component for recommended CakePHP form protection settings.
     * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
     */
    $this->loadComponent('FormProtection');
  }

  /**
   * @inheritdoc
   */
  public function redirect(array|string|UriInterface $url, int $status = 302): ?Response
  {
    if (is_array($url)) {
      $url = UrlTool::url($url);
    }
    return parent::redirect($url, $status);
  }

  #endregion

  #region protected overridable methods

  /**
   * Gets the actions that can be performed without the user being authenticated.
   *
   * The default implementation returns an empty array. Subclasses can override this method.
   *
   * @return string[] List of actions
   */
  protected function getAnonymousActions(): array
  {
    return [];
  }

  /**
   * This method is called to check if authentication should be loaded.
   *
   * The default implementation just returns true
   *
   * @return bool
   */
  protected function useAuthentication(): bool
  {
    return true;
  }

  #endregion

  #region protected methods

  /**
   * Returns the current logged-in user or null if there is no user logged in.
   *
   * @return UserEntity|null Current user
   */
  protected function user(): ?UserEntity
  {
    if (isset($this->m_user)) {
      return $this->m_user;
    }
    $identity = $this->Authentication->getIdentity();
    if (isset($identity)) {
      $usersTable = Tables::users();
      $this->m_user = $usersTable->findForId($identity->get('id'));
      return $this->m_user;
    }
    return null;
  }

  /**
   * Updates the stored logged-in user. Only when the id matched the new record gets set.
   *
   * @param UserEntity $updatedUser
   */
  protected function updateUser(UserEntity $updatedUser): void
  {
    $currentUser = $this->user();
    if (isset($currentUser) && ($currentUser->id === $updatedUser->id)) {
      $this->m_user = $updatedUser;
      $this->Authentication->setIdentity($updatedUser);
    }
  }

  /**
   * Calls {@link FlashComponent::error}
   *
   * @param string $message Error message, can contain html formatting.
   * @param array $options Additional options
   *
   * @return bool Always false
   */
  protected function error(string $message, array $options = []): bool
  {
    $this->Flash->error($message, $options + ['escape' => false]);
    return false;
  }

  /**
   * Calls {@link FlashComponent::success}
   *
   * @param string $message Success message, can contain html formatting.
   * @param array $options Additional options
   *
   * @return bool Always true
   */
  protected function success(string $message, array $options = []): bool
  {
    $this->Flash->success($message, $options + ['escape' => false]);
    return true;
  }

  /**
   * Sets a success message and redirects to an url.
   *
   * @param array|string $url Url or action array to jump to
   * @param string $message Success message, can contain html formatting.
   * @param array $options Additional message options
   *
   * @return Response|null
   */
  protected function redirectWithSuccess(
    array|string $url,
    string $message,
    array $options = []
  ): ?Response {
    $this->success($message, $options);
    return $this->redirect($url);
  }

  /**
   * Sets an error message and redirects to an url.
   *
   * @param array|string $url Url or action array to jump to
   * @param string $message Success message, can contain html formatting.
   * @param array $options Additional message options
   *
   * @return Response|null
   */
  protected function redirectWithError(
    array|string $url,
    string $message,
    array $options = []
  ): ?Response {
    $this->success($message, $options);
    return $this->redirect($url);
  }

  /**
   * Gets the action to return to the home page.
   *
   * @return array
   */
  protected function getHomeAction(): array
  {
    $user = $this->user();
    if (!$user) {
      $this->error(__('You are not logged in.'));
      return AccountController::LOGIN;
    }
    return $user->administrator ? AdministratorController::INDEX : UserController::INDEX;
  }

  /**
   * Removes one or more names from the request. This method can be used to remove for example password fields.
   *
   * @param ...$aNames - names to remove
   *
   * @return ServerRequest Updated request
   */
  protected function withoutData(...$aNames): ServerRequest
  {
    $request = $this->getRequest();
    foreach ($aNames as $name) {
      $request = $request->withoutData($name);
    }
    return $this->getRequest();
  }

  /**
   * Checks if request is a post or put type.
   *
   * @return bool True if request is post and put.
   */
  protected function isSubmit(): bool
  {
    return $this->getRequest()->is(['post', 'put', 'patch']);
  }

  /**
   * Processes the edit profile action.
   *
   * @param array $url Url to redirect to when the profile is updated successfully.
   *
   * @return EditProfileViewModel
   */
  protected function processEditProfile(array $url): EditProfileViewModel
  {
    $viewData = new EditProfileViewModel();
    $user = $this->user();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_EDIT_PROFILE) != null) &&
      $viewData->patch($this->getRequest()->getData())
    ) {
      $user = $this->user();
      $viewData->copyToEntity($user);
      if (Tables::users()->save($user)) {
        $this->updateUser($user);
        $this->redirectWithSuccess(
          $url,
          __('Your profile has been updated.')
        );
        $viewData->clear();
      }
      else {
        $this->error(__('Your profile could not be saved.'));
      }
    }
    else {
      $viewData->copyFromEntity($user);
    }
    return $viewData;
  }

  /**
   * Processes the change password action.
   *
   * @param array $url Url to redirect to when the password is updated successfully.
   *
   * @return ChangePasswordViewModel
   */
  protected function processChangePassword(array $url): ChangePasswordViewModel
  {
    $viewData = new ChangePasswordViewModel();
    if (
      $this->isSubmit() &&
      ($this->getRequest()->getData(self::SUBMIT_CHANGE_PASSWORD) != null) &&
      $viewData->patch($this->getRequest()->getData())
    ) {
      $user = $this->user();
      if ($user->isCorrectPassword($viewData->current_password)) {
        $user->password = $viewData->new_password;
        if (tables::users()->save($user)) {
          $this->redirectWithSuccess(
            $url,
            __('Your password has been updated.'));
        }
        else {
          $this->error(__('An error occurred with the database, your password has not changed.'));
        }
      }
      else {
        $this->error(__('The current password is invalid.'));
      }
    }
    $viewData->clear();
    return $viewData;
  }

  /**
   * Exports data to a csv file for download within the browser.
   *
   * @param string $filename Filename to use for the download.
   * @param array[] $data Rows of column data, each row should contain the same number of columns
   * @param array|null $headers Optional headers for the columns (should be the same length as
   * the rows)
   *
   * @return Response
   */
  protected function exportCsv(string $filename, array $data, ?array $headers = null): Response
  {
    // open a memory stream for the CSV data
    $csvData = fopen('php://memory', 'r+');
    // write the headers to the CSV
    if ($headers) {
      fputcsv($csvData, $headers);
    }
    // write the data to the CSV
    foreach ($data as $row) {
      fputcsv($csvData, $row);
    }
    // rewind the memory stream
    rewind($csvData);
    // Read the CSV data into a string
    $csvContent = stream_get_contents($csvData);
    // close the memory stream
    fclose($csvData);
    // set the response headers for CSV download
    $this->response = $this->response
      ->withType('text/csv')
      ->withHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
      ->withStringBody($csvContent);
    return $this->response;
  }

  #endregion

  #region private methods

  /**
   * Determines the language to use.
   *
   * @param UserEntity|null $user When not null, the user's language is updated.
   *
   * @return void
   */
  private function processLanguage(?UserEntity $user): void {
    $language = $this->getRequest()->getParam(UrlTool::LANGUAGE);
    $language_id = $language == null
      ? $user?->language_id ?? Language::ENGLISH_ID
      : Language::getId($language);
    I18n::setLocale(Language::getCode($language_id));
    if ($user && ($user->language_id != $language_id)) {
      $user->language_id = $language_id;
    }
  }

  #endregion
}

