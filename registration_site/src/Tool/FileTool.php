<?php

namespace App\Tool;

use DateTimeImmutable;

/**
 * {@link FileTool} provides file(name) related utility functions.
 */
class FileTool
{
  /**
   * Gets the last part of the filename after the last dot.
   *
   * @param string $filename
   *
   * @return string Part without leading dot.
   */
  public static function getExtension(string $filename): string
  {
    $parts = explode('.', $filename);
    return end($parts);
  }

  /**
   * Gets the filename without the extension.
   *
   * @param string $filename
   *
   * @return string Filename without extension including the last dot.
   */
  public static function getFilename(string $filename): string
  {
    $parts = explode('.', $filename);
    if (count($parts) < 2) {
      return $parts[0];
    }
    array_pop($parts);
    return implode('.', $parts);
  }

  /**
   * Adds the date and time to the filename.
   *
   * @param string $filename
   * @param DateTimeImmutable $date
   *
   * @return string Filename with date and time: `{name}_{date_time}.{extension}`
   */
  public static function addDateTime(string $filename, DateTimeImmutable $date): string
  {
    $name = self::getFilename($filename);
    $extension = self::getExtension($filename);
    return $name.'_'.$date->format('Y-m-d_H-i').'.'.$extension;
  }

  /**
   * Adds the date to the filename.
   *
   * @param string $filename
   * @param DateTimeImmutable $date
   *
   * @return string Filename with date: `{name}_{date}.{extension}`
   */
  public static function addDate(string $filename, DateTimeImmutable $date): string
  {
    $name = self::getFilename($filename);
    $extension = self::getExtension($filename);
    return $name.'_'.$date->format('Y-m-d').'.'.$extension;
  }
}
