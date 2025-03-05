<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\AbsentUserEntity;
use App\Model\Entity\AbsentUserWithEventEntity;
use App\Model\Entity\EventEntity;
use App\Model\Entity\UserEntity;

class AbsentUsersTable extends TableWithTimestamp
{
  #region cakephp callbacks

  /**
   * @inheritdoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(AbsentUserEntity::class);
    $this
      ->belongsTo(EventsTable::getDefaultAlias())
      ->setForeignKey(AbsentUserEntity::EVENT_ID);
    $this
      ->belongsTo(UsersTable::getDefaultAlias())
      ->setForeignKey(AbsentUserEntity::USER_ID);
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
        AbsentUserEntity::USER_ID => $user->id,
        AbsentUserEntity::EVENT_ID => $event->id
      ])
      ->count();
    if ($count > 0) {
      return;
    }
    /** @var AbsentUserEntity $entity */
    $entity = $this->newEmptyEntity();
    $entity->user_id = $user->id;
    $entity->event_id = $event->id;
    $this->saveOrFail($entity);
  }

  /**
   * @param string $id
   * @return AbsentUserEntity
   */
  public function getForId(string $id): AbsentUserEntity
  {
    /** @var AbsentUserEntity $result */
    $result = $this->get($id);
    return $result;
  }

  /**
   * @param UserEntity $user
   *
   * @return AbsentUserWithEventEntity[]
   */
  public function getAllForUserWithEvent(UserEntity $user): array
  {
    return $this->find('all')
      ->contain(EventsTable::getDefaultAlias())
      ->where([
        AbsentUserEntity::USER_ID => $user->id,
      ])
      ->all()
      ->toList();
  }

  #endregion
}
