<?php

namespace App\Test\Lib;

use App\Lib\Model\Entity\IEntityWithId;
use App\Model\Entity\EventEntity;
use App\Model\Entity\EventWorkshopEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\WorkshopEntity;
use App\Model\Entity\WorkshopTextEntity;
use App\Model\Tables;
use App\Model\Value\Language;
use Cake\Chronos\Chronos;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Closure;
use DateTimeImmutable;

/**
 * Tests should extend this class to get access to the value lookup fixtures.
 */
class TestCaseBase extends TestCase
{
  private ConnectionInterface $m_connection;

  public function getFixtures(): array
  {
    return [
      'app.Languages',
      'app.ParticipantTypes',
    ];
  }

  protected function setUp(): void
  {
    parent::setUp();
    // don't use a fixed time, but return the current time with every call to now
    Chronos::setTestNow();
    // use a transaction to prevent the database from being changed
    $this->m_connection = ConnectionManager::get('default');
    $this->m_connection->begin();
  }

  protected function tearDown(): void
  {
    // throw away all changes
    $this->m_connection->rollback();
    parent::tearDown();
  }

  protected function assertEqualDate(DateTimeImmutable $expected, DateTimeImmutable $actual): void
  {
    $this->assertEquals($expected->format('Y-m-d H:i:s'), $actual->format('Y-m-d H:i:s'));
  }

  /**
   * Checks if two lists contains entities with the same id. For every found pair, the optional
   * testCallback is called that can perform more tests.
   *
   * @param IEntityWithId[] $expectedEntities
   * @param IEntityWithId[] $actualEntities
   * @param Closure(IEntityWithId, IEntityWithId): void | null $testCallback
   */
  protected function assertEqualEntities(
    array $expectedEntities,
    array $actualEntities,
    ?Closure $testCallback = null
  ): void {
    $this->assertEquals(count($expectedEntities), count($actualEntities), 'Count of entities does not match');
    foreach ($expectedEntities as $expectedEntity) {
      $found = false;
      foreach ($actualEntities as $actualEntity) {
        if ($expectedEntity->id === $actualEntity->id) {
          $found = true;
          if ($testCallback) {
            $testCallback($expectedEntity, $actualEntity);
          }
          break;
        }
      }
      if (!$found) {
        $this->fail('Expected entity not found: '.$expectedEntity->id);
      }
    }
  }

  public function text(): string
  {
    return 'mock'.md5(uniqid());
  }

  public function email(): string
  {
    return 'mock'.md5(uniqid()).'@'.md5(uniqid()).'.com';
  }

  public function bool(): bool
  {
    return (bool) rand(0, 1);
  }

  public function createUser(bool $administrator = false): UserEntity
  {
    /** @var UserEntity $user */
    $user = Tables::users()->newEmptyEntity();
    $user->name = $this->text();
    $user->email = $this->email();
    $user->password = $this->text();
    $user->language_id = 1;
    $user->administrator = $administrator;
    $this->assertNotFalse(Tables::users()->save($user));
    return $user;
  }

  public function createFinishedEvent(): EventEntity
  {
    /** @var EventEntity $event */
    $event = Tables::events()->newEmptyEntity();
    $event->event_date = new DateTime('-1 day');
    $event->signup_date = new DateTime('-2 days');
    $this->assertNotFalse(Tables::events()->save($event));
    return $event;
  }

  public function createActiveEvent(): EventEntity
  {
    /** @var EventEntity $event */
    $event = Tables::events()->newEmptyEntity();
    $event->event_date = new DateTime();
    $event->signup_date = new DateTime('-2 days');
    $this->assertNotFalse(Tables::events()->save($event));
    return $event;
  }

  public function createPendingEvent(): EventEntity
  {
    /** @var EventEntity $event */
    $event = Tables::events()->newEmptyEntity();
    $event->event_date = new DateTime('+2 days');
    $event->signup_date = new DateTime('-2 days');
    $this->assertNotFalse(Tables::events()->save($event));
    return $event;
  }

  public function createWorkshop(): WorkshopEntity
  {
    /** @var WorkshopEntity $workshop */
    $workshop = Tables::workshops()->newEmptyEntity();
    $workshop->setText(WorkshopTextEntity::NAME, $this->text(), Language::ENGLISH_ID);
    $workshop->setText(WorkshopTextEntity::DESCRIPTION, $this->text(), Language::ENGLISH_ID);
    $workshop->setText(WorkshopTextEntity::NAME, $this->text(), Language::DUTCH_ID);
    $workshop->setText(WorkshopTextEntity::DESCRIPTION, $this->text(), Language::DUTCH_ID);
    $workshop->laptop = $this->bool();
    $this->assertNotFalse(Tables::workshops()->save($workshop));
    return $workshop;
  }

  public function createEventWorkshop(
    EventEntity $event,
    WorkshopEntity $workshop,
    ?int $placeCount = null
  ): EventWorkshopEntity {
    /** @var EventWorkshopEntity $eventWorkshop */
    $eventWorkshop = Tables::eventWorkshops()->newEmptyEntity();
    $eventWorkshop->event_id = $event->id;
    $eventWorkshop->workshop_id = $workshop->id;
    $eventWorkshop->place_count = $placeCount === null ? rand(2, 20) : $placeCount;
    $this->assertNotFalse(Tables::eventWorkshops()->save($eventWorkshop));
    return $eventWorkshop;
  }

  public function createParticipant(
    UserEntity $user,
    ?EventEntity $event = null,
    ?EventWorkshopEntity $eventFirstWorkshop = null,
    ?EventWorkshopEntity $eventBackupWorkshop = null,
  ): ParticipantEntity {
    /** @var ParticipantEntity $participant */
    $participant = Tables::participants()->newEmptyEntity();
    $participant->name = $this->text();
    $participant->user_id = $user->id;
    $participant->event_id = $event?->id;
    $participant->event_workshop_1_id = $eventFirstWorkshop?->id;
    $participant->event_workshop_2_id = $eventBackupWorkshop?->id;
    $participant->has_laptop = $this->bool();
    $this->assertNotFalse(Tables::participants()->save($participant));
    return $participant;
  }
}
