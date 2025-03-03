<?php

namespace App\Test\Lib;

use Cake\TestSuite\EmailTrait;

/**
 * This class can be used by test cases that need to send emails.
 */
class TestWithMailerCaseBase extends TestCaseBase
{
  use EmailTrait;

  public function setUp(): void
  {
    parent::setUp();
    $this->loadRoutes();
  }
}
