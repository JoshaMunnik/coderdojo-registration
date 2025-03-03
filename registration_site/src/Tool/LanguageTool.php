<?php

namespace App\Tool;

use App\Model\Value\Language;
use Cake\I18n\I18n;
use Closure;

/**
 * {@link LanguageTool} provides language related utility functions.
 */
class LanguageTool
{
  /**
   * Runs the given closure for the specified language.
   *
   * @param int $languageId
   * @param Closure $closure
   *
   * @return void
   */
  public static function runForLanguage(int $languageId, Closure $closure): void {
    $currentLocale = I18n::getLocale();
    I18n::setLocale(Language::getCode($languageId));
    try {
      $closure();
    } finally {
      I18n::setLocale($currentLocale);
    }
  }
}
