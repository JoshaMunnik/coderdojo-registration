<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\ParticipantEntity;
use App\Model\Tables;
use App\Test\Lib\TestCaseBase;

class EventsTableTest extends TestCaseBase {
  public function testEmptyTable() {
    $users = Tables::events()->getAll();
    $this->assertEmpty($users);
  }

  public function testAddEvent() {
    $expected = $this->createPendingEvent();
    $actual = Tables::events()->getForId($expected->id);
    $this->assertEqualDate($expected->event_date, $actual->event_date);
    $this->assertEqualDate($expected->signup_date, $actual->signup_date);
  }

  public function testGetPendingIds() {
    $expected1 = $this->createPendingEvent();
    $expected2 = $this->createPendingEvent();
    $active1 = $this->createActiveEvent();
    $active2 = $this->createActiveEvent();
    $finished1 = $this->createFinishedEvent();
    $finished2 = $this->createFinishedEvent();
    $list = Tables::events()->getPendingIds();
    $this->assertEqualsCanonicalizing([$expected1->id, $expected2->id], $list);
  }

  public function testAnonymizeParticipants() {
    $event1 = $this->createFinishedEvent();
    $event2 = $this->createPendingEvent();
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $eventWorkshop1_1 = $this->createEventWorkshop($event1, $workshop1);
    $eventWorkshop1_2 = $this->createEventWorkshop($event1, $workshop2);
    $eventWorkshop2_1 = $this->createEventWorkshop($event2, $workshop1);
    $eventWorkshop2_2 = $this->createEventWorkshop($event2, $workshop2);
    $participant1_1_1 = $this->createParticipant($user1, $event1, $eventWorkshop1_1);
    $participant1_1_2 = $this->createParticipant($user1, $event1, $eventWorkshop1_2);
    $participant1_2_1 = $this->createParticipant($user1, $event2, $eventWorkshop2_1);
    $participant1_2_2 = $this->createParticipant($user1, $event2, $eventWorkshop2_2);
    $participant2_1_1 = $this->createParticipant($user2, $event1, $eventWorkshop1_1);
    $participant2_1_2 = $this->createParticipant($user2, $event1, $eventWorkshop1_2);
    $participant2_2_1 = $this->createParticipant($user2, $event2, $eventWorkshop2_1);
    $participant2_2_2 = $this->createParticipant($user2, $event2, $eventWorkshop2_2);
    Tables::events()->anonymizeParticipants($event1);
    $participants = Tables::participants()->getAll();
    $this->assertEqualEntities([
      $participant1_1_1, $participant1_1_2, $participant1_2_1, $participant1_2_2,
      $participant2_1_1, $participant2_1_2, $participant2_2_1, $participant2_2_2
    ], $participants,
    function($expected, $actual) use ($event1, $event2, $user1, $user2) {
      /** @var ParticipantEntity $expected */
      /** @var ParticipantEntity $actual */
      if ($actual->event_id == $event1->id) {
        $this->assertEmpty($actual->name);
        $this->assertNull($actual->user_id);
      }
      else {
        $this->assertEquals($expected->name, $actual->name);
        $this->assertEquals($expected->user_id, $actual->user_id);
        $this->assertEquals($event2->id, $actual->event_id);
        $this->assertEquals($expected->event_workshop_1_id, $actual->event_workshop_1_id);
      }
    });
  }
}
