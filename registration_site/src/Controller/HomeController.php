<?php

namespace App\Controller;

use App\Lib\Controller\ApplicationControllerBase;

/**
 * {@link HomeController} handles the start page of the web application.
 */
class HomeController extends ApplicationControllerBase
{
  #region public methods

  public function index()
  {
  }

  #endregion

  #region protected methods

  protected function getAnonymousActions(): array
  {
    return ['index'];
  }

  #endregion
}
