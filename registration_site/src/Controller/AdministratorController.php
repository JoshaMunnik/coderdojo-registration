<?php

namespace App\Controller;

use App\Lib\Controller\AdministratorControllerBase;
use Cake\Cache\Cache;
use Cake\Http\Response;

/**
 * {@link AdministratorController} handles the administrator related actions.
 */
class AdministratorController extends AdministratorControllerBase
{
  #region public constants

  public const INDEX = [self::class, 'index'];
  public const CLEAR_CACHE = [self::class, 'clear-cache'];

  #endregion

  #region public methods

  /**
   * Shows the home page and processes some standard forms.
   *
   * @return Response|null
   */
  public function index(): ?Response
  {
    $this->set('editProfileData', $this->processEditProfile(self::INDEX));
    $this->set('changePasswordData', $this->processChangePassword(self::INDEX));
    return null;
  }

  /**
   * Clears cached data, including translations and model caches.
   *
   * @return Response|null
   */
  public function clearCache(): ?Response
  {
    Cache::clear();
    Cache::clear('_cake_translations_');
    Cache::clear('_cake_model_');
    return $this->redirectWithSuccess(self::INDEX, __('Caches have been cleared.'));
  }

  #endregion
}
