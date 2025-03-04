<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\AbsentParticipantEntity;
use App\Model\Entity\EventEntity;
use App\Model\Entity\UserEntity;

class AbsentParticipantsTable extends TableWithTimestamp
{
  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(AbsentParticipantEntity::class);
    $this
      ->belongsTo(EventsTable::getDefaultAlias())
      ->setForeignKey(EventEntity::ID);
    $this
      ->belongsTo(UsersTable::getDefaultAlias())
      ->setForeignKey(UserEntity::ID);
  }

  #endregion

  #region public methods

  /**
   * Adds an entry for an user and event. If an entry already exists, nothing happens.
   *
   * @param UserEntity $user
   * @param EventEntity $event
   *
   * @return void
   */
  public function addUserAndEvent(UserEntity $user, EventEntity $event): void
  {
    $count = $this->find('all')
      ->where([
        AbsentParticipantEntity::USER_ID => $user->id,
        AbsentParticipantEntity::EVENT_ID => $event->id
      ])
      ->count();
    if ($count > 0) {
      return;
    }
    /** @var AbsentParticipantEntity $entity */
    $entity = $this->newEmptyEntity();
    $entity->user_id = $user->id;
    $entity->event_id = $event->id;
    $this->saveOrFail($entity);
  }

  public function getForId(string $id): AbsentParticipantEntity
  {
    /** @var AbsentParticipantEntity $result */
    $result = $this->get($id);
    return $result;
  }

  public function getAllForUserWithEvent(UserEntity $user): array
  {
    return $this->find('all')
      ->contain(EventsTable::getDefaultAlias())
      ->where([
        AbsentParticipantEntity::USER_ID => $user->id,
      ])
      ->all()
      ->toList();
  }

  #endregion
}
