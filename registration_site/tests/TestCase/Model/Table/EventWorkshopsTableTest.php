<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\EventWorkshopEntity;
use App\Model\Tables;
use App\Test\Lib\TestCaseBase;

class EventWorkshopsTableTest extends TestCaseBase
{
  #region tests

  public function testGetAllContainsCorrectKeys(): void {
    $event = $this->createPendingEvent();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $eventWorkshop1 = $this->createEventWorkshop($event, $workshop1);
    $eventWorkshop2 = $this->createEventWorkshop($event, $workshop2);
    $all = Tables::eventWorkshops()->getAllForEvent($event);
    foreach($all as $key => $value) {
      $this->assertEquals($key, $value->id);
    }
  }

  #endregion
}
