<?php
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace App\Model;

#region traits

use App\Model\Table\EventsTable;
use App\Model\Table\EventWorkshopsTable;
use App\Model\Table\ParticipantsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\WorkshopsTable;
use App\Model\Table\WorkshopTextsTable;
use \Cake\ORM\Locator\LocatorAwareTrait;

#endregion

#region private variables

/**
 * This class can be used to access the table instances. It uses lazy access, fetching the instance
 * the first time a table instance is accessed.
 */
class Tables
{
  #region traits

  use LocatorAwareTrait;

  #endregion

  #region private variables

  /**
   * Reference to singleton instance
   *
   * @var Tables|null
   */
  static ?Tables $s_instance = null;

  /**
   * Reference to singleton instance
   *
   * @var EventsTable|null
   */
  static ?EventsTable $s_events = null;

  /**
   * Reference to singleton instance
   *
   * @var EventWorkshopsTable|null
   */
  static ?EventWorkshopsTable $s_eventWorkshops = null;

  /**
   * Reference to singleton instance
   *
   * @var ParticipantsTable|null
   */
  static ?ParticipantsTable $s_participants = null;

  /**
   * Reference to singleton instance
   *
   * @var UsersTable|null
   */
  static ?UsersTable $s_users = null;

  /**
   * Reference to singleton instance
   *
   * @var WorkshopsTable|null
   */
  static ?WorkshopsTable $s_workshops = null;

  /**
   * Reference to singleton instance
   *
   * @var WorkshopTextsTable|null
   */
  static ?WorkshopTextsTable $s_workshopTexts = null;

  #endregion

  #region private function

  /**
   * Gets the singleton instance.
   *
   * @return Tables Singleton instance
   */
  private static function instance(): Tables
  {
    return self::$s_instance ?? self::$s_instance = new Tables();
  }

  #endregion

  #region constructor

  private function __constructor()
  {
  }

  #endregion

  #region public static methods

  public static function events(): EventsTable
  {
    return self::$s_events
      ?? self::$s_events = self::instance()->fetchTable(
        EventsTable::getDefaultAlias()
      );
  }

  public static function eventWorkshops(): EventWorkshopsTable
  {
    return self::$s_eventWorkshops
      ?? self::$s_eventWorkshops = self::instance()->fetchTable(
        EventWorkshopsTable::getDefaultAlias()
      );
  }

  public static function participants(): ParticipantsTable
  {
    return self::$s_participants
      ?? self::$s_participants = self::instance()->fetchTable(
        ParticipantsTable::getDefaultAlias()
      );
  }

  public static function users(): UsersTable
  {
    return self::$s_users
      ?? self::$s_users = self::instance()->fetchTable(UsersTable::getDefaultAlias());
  }

  public static function workshops(): WorkshopsTable
  {
    return self::$s_workshops
      ?? self::$s_workshops = self::instance()->fetchTable(WorkshopsTable::getDefaultAlias());
  }

  public static function workshopTexts(): WorkshopTextsTable
  {
    return self::$s_workshopTexts
      ?? self::$s_workshopTexts = self::instance()->fetchTable(
        WorkshopTextsTable::getDefaultAlias()
      );
  }

  #endregion
}
