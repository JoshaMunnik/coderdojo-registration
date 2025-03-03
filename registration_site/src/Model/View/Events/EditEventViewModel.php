<?php

namespace App\Model\View\Events;

use App\Model\Entity\EventEntity;
use App\Model\Value\ParticipantType;
use App\Model\View\IdViewModel;
use Cake\Validation\Validator;
use DateTime;
use Exception;

/**
 * A view model for editing an event.
 */
class EditEventViewModel extends IdViewModel
{
  #region field constants

  public const SIGNUP_DATE = 'signup_date';
  public const EVENT_DATE = 'event_date';
  public const PARTICIPANT_TYPE = 'participant_type';

  #endregion

  #region public properties

  public string $event_date = '';
  public string $signup_date = '';
  public int $participant_type = ParticipantType::CHILDREN;
  public readonly array $participant_types;

  #endregion

  #region constructor

  public function __construct()
  {
    parent::__construct();
    $this->participant_types = ParticipantType::getList();
  }

  #endregion

  #region public methods

  /**
   * Copies the data from the view model to the entity.
   */
  public function copyToEntity(EventEntity $entity): void
  {
    $entity->event_date = new DateTime($this->event_date);
    $entity->signup_date = new DateTime($this->signup_date);
    $entity->participant_type = $this->participant_type;
  }

  /**
   * @inheritDoc
   */
  public function clear(): void
  {
    parent::clear();
    $this->event_date = '';
    $this->signup_date = '';
    $this->participant_type = ParticipantType::CHILDREN;
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    return match ($field) {
      self::EVENT_DATE => __('event date'),
      self::SIGNUP_DATE => __('signup date'),
      self::PARTICIPANT_TYPE => __('participant type'),
      default => parent::getFieldName($field),
    };
  }

  #endregion

  #region protected methods

  /**
   * @inheritDoc
   */
  protected function buildValidator(): Validator
  {
    return parent::buildValidator()
      ->dateTime(self::EVENT_DATE)
      ->dateTime(self::SIGNUP_DATE)
      ->add(self::SIGNUP_DATE, 'correctDates', [
        'rule' => fn($value, array $context) => $this->isCorrectDate($value, $context)
      ]);
  }

  #endregion

  #region private methods

  /**
   * Check if the signup date comes before the event date.
   *
   * @param string $aValue
   * @param array $aContext
   *
   * @return bool|string
   *
   * @throws Exception
   */
  private function isCorrectDate(string $aValue, array $aContext): bool|string
  {
    $signupDate = new DateTime($aContext['data'][self::SIGNUP_DATE]);
    $eventDate = new DateTime($aContext['data'][self::EVENT_DATE]);
    if ($signupDate < $eventDate) {
      return true;
    }
    return __('The signup date must start before the date of the event.');
  }

  #endregion
}
