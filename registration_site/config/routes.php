<?php

use App\Tool\UrlTool;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
  /*
   * The default class to use for all routes
   *
   * The following route classes are supplied with CakePHP and are appropriate
   * to set as the default:
   *
   * - Route
   * - InflectedRoute
   * - DashedRoute
   *
   * If no call is made to `Router::defaultRouteClass()`, the class used is
   * `Route` (`Cake\Routing\Route\Route`)
   *
   * Note that `Route` does not do any inflections on URLs which will result in
   * inconsistently cased URLs when used with `{plugin}`, `{'.UrlTool::CONTROLLER.'}` and
   * `{'.UrlTool::ACTION.'}` markers.
   */
  $routes->setRouteClass(DashedRoute::class);
  $routes
    ->connect(
      '/',
      [
        UrlTool::CONTROLLER => 'Home',
        UrlTool::ACTION => 'index',
        UrlTool::LANGUAGE => 'en',
      ],
    );
  $routes
    ->connect(
      '/{'.UrlTool::LANGUAGE.'}',
      [
        UrlTool::CONTROLLER => 'Home',
        UrlTool::ACTION => 'index',
      ],
    )
    ->setPatterns(
      [UrlTool::LANGUAGE => '[a-z]{2}'],
    );
  $routes
    ->connect(
      '/login',
      [
        UrlTool::CONTROLLER => 'Account',
        UrlTool::ACTION => 'login',
        UrlTool::LANGUAGE => 'en',
      ],
    );
  $routes
    ->connect(
      '/logout',
      [
        UrlTool::CONTROLLER => 'Account',
        UrlTool::ACTION => 'logout',
        UrlTool::LANGUAGE => 'en',
      ],
    );
  $routes
    ->connect(
      '/{'.UrlTool::LANGUAGE.'}/{'.UrlTool::CONTROLLER.'}',
      [
        UrlTool::ACTION => 'index',
      ],
    )
    ->setPatterns(
      [UrlTool::LANGUAGE => '[a-z]{2}'],
    );
  $routes
    ->connect(
      '/{'.UrlTool::LANGUAGE.'}/{'.UrlTool::CONTROLLER.'}/{'.UrlTool::ACTION.'}/*',
    )
    ->setPatterns(
      [UrlTool::LANGUAGE => '[a-z]{2}'],
    );
  $routes
    ->connect(
      '/{'.UrlTool::CONTROLLER.'}',
      [
        UrlTool::ACTION => 'index',
        UrlTool::LANGUAGE => 'en',
      ],
    );
  $routes
    ->connect(
      '/{'.UrlTool::CONTROLLER.'}/{'.UrlTool::ACTION.'}/*',
      [
        UrlTool::LANGUAGE => 'en',
      ]
    );
  $routes->fallbacks();
};
