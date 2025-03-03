<?php

namespace App\Test\TestCase\Tool;

use App\Model\Entity\EventEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Table\ParticipantsTable;
use App\Model\Tables;
use App\Test\Lib\TestWithMailerCaseBase;
use App\Tool\ParticipantTool;
use Cake\I18n\DateTime;
use DateTimeImmutable;

class ParticipantToolTest extends TestWithMailerCaseBase
{
  #region private variables

  /**
   * @var ParticipantEntity[]
   */
  private array $participants;

  private EventEntity $event;


  #endregion

  #region public methods

  public function testAddToEmptyWorkshop() {
    $user = $this->createUser();
    $event = $this->createPendingEvent();
    $workshop = $this->createWorkshop();
    $eventWorkshop = $this->createEventWorkshop($event, $workshop, 2);
    $expected = $this->createParticipant($user, $event, $eventWorkshop);
    ParticipantTool::joinFirstWorkshop($user, $expected, $event, $eventWorkshop);
    $this->assertEquals($expected->event_workshop_1_id, $eventWorkshop->id);
    $this->assertNotNull($expected->event_workshop_1_join_date);
    $this->assertNotNull($expected->event_workshop_1_notify_date);
    $actual = Tables::participants()->getForId($expected->id);
    $this->assertEquals($actual->event_workshop_1_id, $eventWorkshop->id);
    $this->assertEqualDate(
      $expected->event_workshop_1_join_date, $actual->event_workshop_1_join_date
    );
    $this->assertEqualDate(
      $expected->event_workshop_1_notify_date, $actual->event_workshop_1_notify_date
    );
    $this->assertMailSentTo($user->email);
  }

  public function testAddToFullWorkshop()
  {
    $user = $this->createUser();
    $event = $this->createPendingEvent();
    $workshop = $this->createWorkshop();
    $eventWorkshop = $this->createEventWorkshop($event, $workshop, 2);
    $participant1 = $this->createParticipant($user, $event, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant1, $eventWorkshop);
    $participant2 = $this->createParticipant($user, $event, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant2, $eventWorkshop);
    $expected = $this->createParticipant($user, $event, $eventWorkshop);
    ParticipantTool::joinFirstWorkshop($user, $expected, $event, $eventWorkshop);
    $this->assertEquals($expected->event_workshop_1_id, $eventWorkshop->id);
    $this->assertNotNull($expected->event_workshop_1_join_date);
    $this->assertNull($expected->event_workshop_1_notify_date);
    $actual = Tables::participants()->getForId($expected->id);
    $this->assertEquals($expected->name, $actual->name);
    $this->assertEquals($actual->event_workshop_1_id, $eventWorkshop->id);
    $this->assertEqualDate(
      $expected->event_workshop_1_join_date, $actual->event_workshop_1_join_date
    );
    $this->assertNull($actual->event_workshop_1_notify_date);
    $this->assertMailSentTo($user->email);
    $actualEventWorkshop = Tables::eventWorkshops()->getForIdWithParticipants($eventWorkshop->id);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $this->assertEquals(3, count($participants));
    $this->assertEquals(1, $actualEventWorkshop->getWaitingPosition($expected));
  }

  public function testAddToFullWorkshopAndThenLeave() {
    $user = $this->createUser();
    $event = $this->createPendingEvent();
    $workshop = $this->createWorkshop();
    $eventWorkshop = $this->createEventWorkshop($event, $workshop, 2);
    $participant1 = $this->createParticipant($user, $event, $eventWorkshop);
    $participant2 = $this->createParticipant($user, $event, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant1, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant2, $eventWorkshop);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $this->assertEquals(0, ParticipantTool::getPosition($participants, $participant1));
    $this->assertEquals(1, ParticipantTool::getPosition($participants, $participant2));
    ParticipantTool::leaveFirstWorkshop($user, $participant2);
    $this->assertMailSentTo($user->email);
    $actual2 = Tables::participants()->getForId($participant2->id);
    $this->assertNull($actual2->event_workshop_1_id);
    $this->assertNull($actual2->event_workshop_1_join_date);
    $this->assertNull($actual2->event_workshop_1_notify_date);
    $this->assertEquals($participant2->name, $actual2->name);
  }

  public function testAddToFullWorkshopAndThenCanParticipate() {
    $user = $this->createUser();
    $event = $this->createPendingEvent();
    $workshop = $this->createWorkshop();
    $eventWorkshop = $this->createEventWorkshop($event, $workshop, 2);
    $participant1 = $this->createParticipant($user, $event, $eventWorkshop);
    $participant2 = $this->createParticipant($user, $event, $eventWorkshop);
    $participant3 = $this->createParticipant($user, $event, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant1, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant2, $eventWorkshop);
    Tables::participants()->addToFirstWorkshop($participant3, $eventWorkshop);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $this->assertEquals(2, ParticipantTool::getPosition($participants, $participant3));
    $this->assertEquals(1, $eventWorkshop->getWaitingPosition($participant3));
    ParticipantTool::leaveFirstWorkshop($user, $participant2);
    $this->assertMailSentTo($user->email);
    $actual2 = Tables::participants()->getForId($participant2->id);
    $this->assertNull($actual2->event_workshop_1_id);
    $this->assertNull($actual2->event_workshop_1_join_date);
    $this->assertNull($actual2->event_workshop_1_notify_date);
    $actual3 = Tables::participants()->getForId($participant3->id);
    $this->assertNotNull($actual3->event_workshop_1_notify_date);
    $participants = Tables::participants()->getAllForWorkshop($eventWorkshop->id);
    $this->assertEquals(1, ParticipantTool::getPosition($participants, $actual3));
    $actualEventWorkshop = Tables::eventWorkshops()->getForIdWithParticipants($eventWorkshop->id);
    $this->assertEquals(2, count($participants));
    $this->assertEquals(0, $actualEventWorkshop->getWaitingPosition($participant3));
  }

  #endregion

  #region private methods

  private function setupData()
  {
  }

  #endregion
}
