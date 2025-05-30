<?php

namespace App\Model\Enum;

/**
 * Optional styling for a table cell.
 */
enum CellStylingEnum
{
  /**
   * Try to let a cell not use more then the biggest content in the column.
   */
  case TIGHT;

  /**
   * Style text as date.
   */
  case DATE;

  /**
   * Hide the cell on mobile.
   */
  case HIDE_ON_MOBILE;
}
