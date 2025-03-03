<?php

namespace App\Model\Value;

/**
 * Defines the participant types and support methods.
 */
readonly class ParticipantType
{
  #region constants

  public const CHILDREN = 1;

  public const ALL = 2;

  #endregion

  #region public methods

  /**
   * Gets the name for a value.
   *
   * @param int $value
   *
   * @return string
   */
  public static function getName(int $value): string {
    return self::getList()[$value];
  }

  /**
   * @return string[] List of all participant types.
   */
  public static function getList(): array {
    return [
      self::CHILDREN => __('Children'),
      self::ALL => __('All'),
    ];
  }

  #endregion
}
