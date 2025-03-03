<?php

namespace App\Model\Value;

/**
 * Defines the language ids and support methods.
 */
class Language
{
  #region constants

  /**
   * English language id, maps to database lookup table entry
   */
  public const ENGLISH_ID = 1;

  /**
   * Dutch language id, maps to database lookup table entry
   */
  public const DUTCH_ID = 2;

  /**
   * English language code
   */
  public const ENGLISH_CODE = 'en';

  /**
   * Dutch language code
   */
  public const DUTCH_CODE = 'nl';

  #endregion

  #region public methods

  /**
   * Gets the name of a language.
   *
   * @param int $language One of the language constants
   *
   * @return string Name
   */
  public static function getName(int $language): string
  {
    return self::getList()[$language];
  }

  /**
   * Gets the code for an language id.
   *
   * @param int $language
   *
   * @return string
   */
  public static function getCode(int $language): string
  {
    return match ($language) {
      self::DUTCH_ID => self::DUTCH_CODE,
      default => self::ENGLISH_CODE,
    };
  }

  /**
   * Gets the id for a language code.
   *
   * @param string $code
   *
   * @return int
   */
  public static function getId(string $code): int
  {
    return match ($code) {
      self::DUTCH_CODE => self::DUTCH_ID,
      default => self::ENGLISH_ID,
    };
  }

  /**
   * Gets a list of all language ids.
   *
   * @return int[]
   */
  public static function getIds(): array
  {
    return array_keys(self::getList());
  }

  /**
   * Gets list of all languages.
   *
   * @return string[] The languages (key is language id, value is language name)
   */
  public static function getList(): array
  {
    return [
      self::ENGLISH_ID => __('English'),
      self::DUTCH_ID => __('Nederlands'),
    ];
  }

  #endregion
}
