<?php

namespace App\Model\Enum;

/**
 * The type of the content of a table cell in a sorted table.
 */
enum CellDataTypeEnum
{
  case TEXT;
  case NUMBER;
  case DATE;
}
