<?php
declare(strict_types=1);

namespace App\View;

use App\Lib\Model\View\ViewModelBase;
use App\Model\Entity\UserEntity;
use App\Tool\UrlTool;
use App\View\Helper\StylingHelper;
use Cake\View\View;
use Exception;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 *
 * @property StylingHelper $Styling
 */
class ApplicationView extends View
{
  /**
   * Initialization hook method.
   *
   * Use this method to add common initialization code like adding helpers.
   *
   * e.g. `$this->addHelper('Html');`
   *
   * @return void
   */
  public function initialize(): void
  {
    $this->loadHelper('Styling');
  }

  /**
   * Shortcut, that maps to {@link UrlTool::url}
   *
   * @param string|array $url
   *
   * @return array
   */
  public function url(string|array $url): array
  {
    return UrlTool::url($url);
  }

  /**
   * Creates a form, forcing only the context as the value source.
   *
   * @param ViewModelBase $model
   * @param string|array $url
   * @return string
   */
  public function createForm(ViewModelBase $model, string|array $url = ''): string
  {
    return $this->Form->create(
      $model,
      [
        'url' => $this->url($url),
        'templates' => 'form_styles',
        'valueSources' => ['context'],
        'idPrefix' => get_class($model),
      ]
    );
  }

  /**
   * Checks if there is a authenticated user and the user is an administrator.
   *
   * @return bool
   */
  public function isAdministrator(): bool
  {
    $user = $this->getRequest()->getAttribute('identity');
    return $user && $user->get(UserEntity::ADMINISTRATOR);
  }

  /**
   * Gets the name of the authenticated user.
   *
   * @return string Name or empty string if no user is authenticated.
   */
  public function userName(): string {
    $user = $this->getRequest()->getAttribute('identity');
    return $user?->get(UserEntity::NAME) ?? '';
  }

  /**
   * Checks if the authenticated user is the given user.
   *
   * @param UserEntity $someUser
   *
   * @return bool True if there is an authenticated user and their ids match.
   */
  public function isUser(UserEntity $someUser): bool {
    $user = $this->getRequest()->getAttribute('identity');
    return $user?->get(UserEntity::ID) === $someUser->id;
  }
}
