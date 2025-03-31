<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserWithParticipantsEntity;
use App\Model\Tables;
use App\Test\Lib\TestCaseBase;

class UsersTableTest extends TestCaseBase
{
  public function testEmptyTable()
  {
    $users = Tables::users()->getAll();
    $this->assertEmpty($users);
  }

  public function testAddUser()
  {
    $expected = $this->createUser();
    $actual = Tables::users()->getForId($expected->id);
    $this->assertEquals($expected->name, $actual->name);
    $this->assertEquals($expected->email, $actual->email);
    $this->assertEquals($expected->password, $actual->password);
    $this->assertEquals($expected->language_id, $actual->language_id);
    $this->assertEquals($expected->administrator, $actual->administrator);
    $this->assertNotEmpty($actual->id);
    $this->assertNotEmpty($actual->created);
    $this->assertNotEmpty($actual->modified);
  }

  public function testRemoveUserWithParticipantsForPendingEvent()
  {
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $event = $this->createPendingEvent();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $eventWorkshop1 = $this->createEventWorkshop($event, $workshop1);
    $eventWorkshop2 = $this->createEventWorkshop($event, $workshop2);
    $participant1_1 = $this->createParticipant($user1, $event, $eventWorkshop1);
    $participant1_2 = $this->createParticipant($user1, $event, $eventWorkshop2);
    $participant2_1 = $this->createParticipant($user2, $event, $eventWorkshop1);
    $participant2_2 = $this->createParticipant($user2, $event, $eventWorkshop2);
    Tables::users()->deleteAndUpdateParticipants($user1);
    $participants = Tables::participants()->getAll();
    $this->assertEqualEntities([$participant2_1, $participant2_2], $participants);
  }

  public function testRemoveUserWithParticipantsForActiveEvent()
  {
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $event = $this->createActiveEvent();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $eventWorkshop1 = $this->createEventWorkshop($event, $workshop1);
    $eventWorkshop2 = $this->createEventWorkshop($event, $workshop2);
    $participant1_1 = $this->createParticipant($user1, $event, $eventWorkshop1);
    $participant1_2 = $this->createParticipant($user1, $event, $eventWorkshop2);
    $participant2_1 = $this->createParticipant($user2, $event, $eventWorkshop1);
    $participant2_2 = $this->createParticipant($user2, $event, $eventWorkshop2);
    Tables::users()->deleteAndUpdateParticipants($user1);
    $participants = Tables::participants()->getAll();
    $this->assertEqualEntities(
      [$participant2_1, $participant2_2, $participant1_1, $participant1_2],
      $participants,
      function($expected, $actual) use ($participant1_1, $participant1_2) {
        /** @var ParticipantEntity $expected */
        /** @var ParticipantEntity $actual */
        if (($actual->id !== $participant1_1->id) && ($actual->id !== $participant1_2->id)) {
          return;
        }
        $this->assertEmpty($actual->name);
        $this->assertNull($actual->user_id);
      }
    );
  }

  public function testRemoveUserWithParticipantsForFinishedEvent()
  {
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $event = $this->createFinishedEvent();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $eventWorkshop1 = $this->createEventWorkshop($event, $workshop1);
    $eventWorkshop2 = $this->createEventWorkshop($event, $workshop2);
    $participant1_1 = $this->createParticipant($user1, $event, $eventWorkshop1);
    $participant1_2 = $this->createParticipant($user1, $event, $eventWorkshop2);
    $participant2_1 = $this->createParticipant($user2, $event, $eventWorkshop1);
    $participant2_2 = $this->createParticipant($user2, $event, $eventWorkshop2);
    Tables::users()->deleteAndUpdateParticipants($user1);
    $participants = Tables::participants()->getAll();
    $this->assertEqualEntities(
      [$participant2_1, $participant2_2, $participant1_1, $participant1_2],
      $participants,
      function($expected, $actual) use ($participant1_1, $participant1_2) {
        /** @var ParticipantEntity $expected */
        /** @var ParticipantEntity $actual */
        if (($actual->id !== $participant1_1->id) && ($actual->id !== $participant1_2->id)) {
          return;
        }
        $this->assertEmpty($actual->name);
        $this->assertNull($actual->user_id);
      }
    );
  }

  public function testGetAbsentParticipants() {
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $user3 = $this->createUser();
    $event = $this->createFinishedEvent();
    $workshop = $this->createWorkshop();
    $eventWorkshop = $this->createEventWorkshop($event, $workshop);
    $participant1_1 = $this->createParticipant($user1, $event, $eventWorkshop);
    $participant1_2 = $this->createParticipant($user1, $event, $eventWorkshop);
    $participant2_1 = $this->createParticipant($user2, $event, $eventWorkshop);
    $participant2_2 = $this->createParticipant($user2, $event, $eventWorkshop);
    $participant3_1 = $this->createParticipant($user3, $event, $eventWorkshop);
    $participant3_2 = $this->createParticipant($user3, $event, $eventWorkshop);
    Tables::participants()->checkin($participant2_1);
    Tables::participants()->checkin($participant2_2);
    $actualUsers = Tables::users()->getAllUsersWithAbsentParticipants($event);
    $this->assertEqualEntities([$user1, $user3], $actualUsers);
  }

  public function testRemoveUserWithParticipantsForMultipleEvents()
  {
    $user = $this->createUser();
    $pendingEvent = $this->createPendingEvent();
    $activeEvent = $this->createActiveEvent();
    $finishedEvent = $this->createFinishedEvent();
    $workshop1 = $this->createWorkshop();
    $workshop2 = $this->createWorkshop();
    $pendingEventWorkshop1 = $this->createEventWorkshop($pendingEvent, $workshop1);
    $pendingEventWorkshop2 = $this->createEventWorkshop($pendingEvent, $workshop2);
    $activeEventWorkshop1 = $this->createEventWorkshop($activeEvent, $workshop1);
    $activeEventWorkshop2 = $this->createEventWorkshop($activeEvent, $workshop2);
    $finishedEventWorkshop1 = $this->createEventWorkshop($finishedEvent, $workshop1);
    $finishedEventWorkshop2 = $this->createEventWorkshop($finishedEvent, $workshop2);
    $pendingParticipant1 = $this->createParticipant($user, $pendingEvent, $pendingEventWorkshop1);
    $pendingParticipant2 = $this->createParticipant($user, $pendingEvent, $pendingEventWorkshop2);
    $activeParticipant1 = $this->createParticipant($user, $activeEvent, $activeEventWorkshop1);
    $activeParticipant2 = $this->createParticipant($user, $activeEvent, $activeEventWorkshop2);
    $finishedParticipant1 = $this->createParticipant($user, $finishedEvent, $finishedEventWorkshop1);
    $finishedParticipant2 = $this->createParticipant($user, $finishedEvent, $finishedEventWorkshop2);
    Tables::users()->deleteAndUpdateParticipants($user);
    $actualParticipants = Tables::participants()->getAll();
    $this->assertEqualEntities(
      [
        $activeParticipant1, $activeParticipant2, $finishedParticipant1, $finishedParticipant2,
      ],
      $actualParticipants
    );
  }

  public function testGetUserWithParticipantsForEvent()
  {
    $user1 = $this->createUser();
    $user2 = $this->createUser();
    $user3 = $this->createUser();
    $event1 = $this->createActiveEvent();
    $event2 = $this->createActiveEvent();
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
    $participant3_2_1 = $this->createParticipant($user3, $event2, $eventWorkshop2_1);
    $participant3_2_2 = $this->createParticipant($user3, $event2, $eventWorkshop2_2);
    $actualUsers = Tables::users()->getAllUsersWithParticipants($event1);
    $this->assertEqualEntities(
      [$user1, $user2],
      $actualUsers,
      function($expected, $actual) use ($participant1_1_1, $participant1_1_2, $participant2_1_1, $participant2_1_2, $user1, $user2) {
        /** @var UserEntity $expected */
        /** @var UserWithParticipantsEntity $actual */
        if ($actual->id === $user1->id) {
          $this->assertEqualEntities([$participant1_1_1, $participant1_1_2], $actual->participants);
          return;
        }
        if ($actual->id === $user2->id) {
          $this->assertEqualEntities([$participant2_1_1, $participant2_1_2], $actual->participants);
          return;
        }
      }
    );
  }


}
