<?php

namespace App\Model\Table;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\AbsentUserEntity;
use App\Model\Entity\EventEntity;
use App\Model\Entity\ParticipantEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserWithParticipantsAndAbsentUsersEntity;
use App\Model\Tables;
use Cake\Validation\Validator;

/**
 * This table encapsulates the users, who can log in to the system and add participants to events
 * and manage (if they have access).
 *
 * @property ParticipantsTable $Participants
 */
class UsersTable extends TableWithTimestamp
{
  #region cakephp callbacks

  /**
   * @inheritDoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(UserEntity::class);
    $this
      ->hasMany(ParticipantsTable::getDefaultAlias())
      ->setForeignKey(ParticipantEntity::USER_ID);
    $this
      ->hasMany(AbsentUsersTable::getDefaultAlias())
      ->setForeignKey(AbsentUserEntity::USER_ID);
  }

  /**
   * @inheritDoc
   */
  public function validationDefault(Validator $validator): Validator
  {
    $validator
      ->requirePresence([UserEntity::NAME, UserEntity::EMAIL, UserEntity::PASSWORD], 'create')
      ->notEmptyString(UserEntity::NAME)
      ->email(UserEntity::EMAIL);
    return $validator;
  }

  #endregion

  #region public methods

  /**
   * Checks if email is not used.
   *
   * @param string $email
   * @param string|null $id When not null, ignore user with this id.
   *
   * @return bool
   */
  public function isUnusedEmail(string $email, string|null $id = ''): bool
  {
    $email = strtolower($email);
    if (empty($id)) {
      return $this
          ->find()
          ->where([UserEntity::EMAIL => $email])
          ->count() === 0;
    }
    return $this
        ->find()
        ->where([
          UserEntity::EMAIL => $email,
          UserEntity::ID.' !=' => $id
        ])
        ->count() === 0;
  }

  /**
   * Checks if a password reset token is known and has not expired.
   *
   * @param string $code
   *
   * @return bool
   */
  public function validateResetToken(string $code): bool
  {
    return $this->find()
        ->where([
          UserEntity::PASSWORD_RESET_TOKEN => $code,
          UserEntity::PASSWORD_RESET_DATE.' >=' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ])
        ->count() === 1;
  }

  /**
   * Tries to find a user for a password reset token.
   *
   * @param string $code
   *
   * @return UserEntity|null
   */
  public function findUserForResetToken(string $code): UserEntity|null
  {
    return $this->find()
      ->where([
        UserEntity::PASSWORD_RESET_TOKEN => $code,
        UserEntity::PASSWORD_RESET_DATE.' >=' => date('Y-m-d H:i:s', strtotime('-1 day'))
      ])
      ->first();
  }

  /**
   * Tries to find a user by an email address.
   *
   * @param string $email
   *
   * @return UserEntity|null
   */
  public function findForEmail(string $email): UserEntity|null
  {
    $email = strtolower($email);
    return $this->find()
      ->where([UserEntity::EMAIL => $email])
      ->first();
  }

  /**
   * Tries to find a user by an id.
   *
   * @param string $id
   *
   * @return UserEntity|null
   */
  public function findForId(string $id): UserEntity|null
  {
    return $this->find()
      ->where([IEntityWithId::ID => $id])
      ->first();
  }

  /**
   * Gets a user entity for an id.
   *
   * @param string $id
   *
   * @return UserEntity
   */
  public function getForId(string $id): UserEntity
  {
    /** @var UserEntity $result */
    $result = $this->get($id);
    return $result;
  }

  /**
   * Gets all user entities.
   *
   * @return UserEntity[]
   */
  public function getAll(): array
  {
    return $this->find('all')->all()->toList();
  }

  /**
   * Gets all user entities with related participants.
   *
   * @return UserWithParticipantsAndAbsentUsersEntity[]
   */
  public function getAllWithParticipantsAndAbsentParticipants(): array
  {
    return $this
      ->find('all')
      ->contain([
        ParticipantsTable::getDefaultAlias(),
        AbsentUsersTable::getDefaultAlias()
      ])
      ->all()
      ->toList();
  }

  /**
   * Deletes the user from the database and updates the participants.
   *
   * Participants for events that have not yet happened will be deleted.
   *
   * Participants for active and finished events will be anonymized.
   *
   * @param UserEntity $user
   *
   * @return void
   */
  public function deleteAndUpdateParticipants(UserEntity $user): void
  {
    $pendingEventIds = Tables::events()->getPendingIds();
    // anonymize participants for events that are not pending
    if (!empty($pendingEventIds)) {
      $this->Participants->updateQuery()
        ->set([
          ParticipantEntity::NAME => '',
          ParticipantEntity::USER_ID => null,
        ])
        ->where([
          ParticipantEntity::EVENT_ID.' NOT IN' => $pendingEventIds,
          ParticipantEntity::USER_ID => $user->id,
        ])
        ->execute();
      // remove all participants for events that are still pending
      $this->Participants->deleteQuery()
        ->where([
          ParticipantEntity::EVENT_ID.' IN' => $pendingEventIds,
          ParticipantEntity::USER_ID => $user->id,
        ])
        ->execute();
    }
    else {
      $this->Participants->updateQuery()
        ->set([
          ParticipantEntity::NAME => '',
          ParticipantEntity::USER_ID => null,
        ])
        ->where([
          ParticipantEntity::USER_ID => $user->id,
        ])
        ->execute();
    }
    // as last step delete the user
    $this->delete($user);
  }

  /**
   * Gets all users that have one or more participants for the event that don't have a check in
   * date. This method does not check if the event is pending, active or finished.
   *
   * @param EventEntity $event
   * @return UserEntity[]
   */
  public function getAllUsersWithAbsentParticipants(EventEntity $event): array
  {
    return $this
      ->find('all')
      ->matching(ParticipantsTable::getDefaultAlias(), fn($query) => $query->where([
          ParticipantEntity::CHECKIN_DATE.' IS' => null,
          ParticipantEntity::EVENT_ID => $event->id
        ]
      ))
      ->all()
      ->toList();
  }

  #endregion
}
