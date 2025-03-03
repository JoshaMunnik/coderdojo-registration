<?php

namespace App\Model\Enum;

/**
 * The position of some content in its container.
 */
enum ContentPositionEnum : int
{
  case START = 0;
  case CENTER = 1;
  case END = 2;
}
