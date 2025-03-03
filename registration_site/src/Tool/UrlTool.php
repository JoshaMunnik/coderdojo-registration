<?php

namespace App\Tool;

use Cake\I18n\I18n;
use Cake\Utility\Inflector;

/**
 * {@link UrlTool} provides utility functions for urls.
 */
class UrlTool
{
  #region public constants

  /**
   * Name of controller
   */
  public const CONTROLLER = 'controller';

  /**
   * Name of action
   */
  public const ACTION = 'action';

  /**
   * Name of prefix
   */
  public const PREFIX = 'prefix';

  /**
   * Name of plugin
   */
  public const PLUGIN = 'plugin';

  /**
   * Name of language
   */
  public const LANGUAGE = 'language';

  #endregion

  #region public methods

  /**
   * This method checks if the url contains a controller class name: a text value that ends with
   * the text 'Controller'.
   *
   * If it does, the method will replace it with a 'controller' key and a dasherized version of
   * the value removing first the 'Controller' at the end.
   *
   * If the first value or the value after the 'controller' is a value without string key, it is
   * assumed to be the action and is replaced by 'action' => value.
   *
   * If the first value is an array, it will process the array and merge with the other elements.
   *
   * This class assumes the controller is always specified before the action.
   *
   * ````php
   * // will all give ['controller' => 'users', 'action' => 'edit', 1]
   * UFUrl::update(['UsersController', 'edit', 1]);
   * UFUrl::update([UsersController::class, 'edit', 1]);
   * UFUrl::update([[UsersController::class, 'edit'], 1]);
   * // shortcut to an action and controller
   * class SomeController {
   *   public const EDIT = [self::class, 'edit];
   * }
   * // will all give ['controller' => 'some', 'action' => 'edit', 10]
   * UFUrl::update(SomeController::EDIT, 10);
   * UFUrl::update([SomeController:EDIT, 10]);
   * ````
   *
   * @param array | string $url Action url to process
   * @param mixed ...$parameters Additional parameters to add to the action url
   *
   * @return array Updated url
   */
  public static function url(array | string $url, ...$parameters): array
  {
    if (is_string($url)) {
      $url = [$url];
    }
    if (!isset($url[UrlTool::LANGUAGE])) {
      $url['language'] = I18n::getLocale() ?? 'en';
    }
    if (count($url) > 0) {
      $values = array_values($url);
      if (is_array($values[0])) {
        $processed = self::url(array_shift($url));
        $url = array_merge($processed, $url);
      }
    }
    $url = array_merge($url, $parameters);
    $processedAction = key_exists(self::ACTION, $url);
    // exit with original url if there is both an action and controller key (nothing to do)
    if ($processedAction && key_exists(self::CONTROLLER, $url)) {
      return $url;
    }
    // copy items, processing values without text key
    $checkAction = true;
    $result = [];
    foreach ($url as $key => $value) {
      // parameter without text key?
      if (is_int($key)) {
        if (str_ends_with($value, 'Controller')) {
          $result[self::CONTROLLER] = self::controllerName($value);
          // act like controller was processed
          $key = self::CONTROLLER;
        }
        elseif ($checkAction && !$processedAction) {
          $result[self::ACTION] = $value;
          $processedAction = true;
        }
        else {
          $result[] = $value;
        }
      }
      else {
        $result[$key] = $value;
      }
      $checkAction = $key == self::CONTROLLER;
    }
    return $result;
  }

  /**
   * Returns the controller name for use within urls from a controller class name. It is assumed
   * the controller class names ends with 'Controller'.
   *
   * @param string $className Class name of controller
   *
   * @return string Dasherized version of the class name without namespace and the ending 'Controller'.
   */
  public static function controllerName(string $className): string
  {
    // 10 is the number characters of 'Controller'
    return Inflector::dasherize(substr(namespaceSplit($className)[1], 0, -10));
  }

  #endregion
}
