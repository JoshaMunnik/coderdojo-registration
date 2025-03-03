<?php

namespace App\Model\Enum;

/**
 * The colors for a normal button.
 */
enum ButtonColorEnum
{
  case PRIMARY;
  case SECONDARY;
  case TERTIARY;
  case SUCCESS;
  case DANGER;
  case WARNING;
  case DISABLED;
}
