<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Tables;
use App\Model\Value\Language;
use App\Test\Lib\TestCaseBase;

class WorkshopsTableTest extends TestCaseBase {
  public function testEmptyTable() {
    $workshops = Tables::workshops()->getAll();
    $this->assertEmpty($workshops);
  }

  public function testAddWorkshop() {
    $expected = $this->createWorkshop();
    $this->assertNotFalse(Tables::workshops()->save($expected));
    $actual = Tables::workshops()->getForId($expected->id);
    $this->assertEquals($expected->getName(Language::DUTCH_ID), $actual->getName(Language::DUTCH_ID));
    $this->assertEquals($expected->getName(Language::ENGLISH_ID), $actual->getName(Language::ENGLISH_ID));
    $this->assertEquals($expected->getDescription(Language::DUTCH_ID), $actual->getDescription(Language::DUTCH_ID));
    $this->assertEquals($expected->getDescription(Language::ENGLISH_ID), $actual->getDescription(Language::ENGLISH_ID));
    $this->assertNotEmpty($actual->id);
    $this->assertNotEmpty($actual->created);
    $this->assertNotEmpty($actual->modified);
  }
}
