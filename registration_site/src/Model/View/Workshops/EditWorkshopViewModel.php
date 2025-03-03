<?php

namespace App\Model\View\Workshops;

use AllowDynamicProperties;
use App\Model\Entity\WorkshopEntity;
use App\Model\Entity\WorkshopTextEntity;
use App\Model\Value\Language;
use App\Model\View\IdViewModel;
use Cake\View\Form\ContextInterface;

/**
 * View model for editing a workshop.
 *
 * This field creates dynamic properties for each language for the name and description.
 */
#[AllowDynamicProperties] class EditWorkshopViewModel extends IdViewModel
{
  #region field constants

  public const LAPTOP = 'laptop';

  #endregion

  #region private constants

  private const NAME_PREFIX = 'name_';
  private const DESCRIPTION_PREFIX = 'description_';

  #endregion

  #region public properties

  public bool $laptop = true;

  #endregion

  #region constructor

  public function __construct()
  {
    $this->clear();
    parent::__construct();
  }

  #endregion

  #region ContextInterface

  /**
   * @inheritdoc
   */
  public function val(string $field, array $options = []): mixed
  {
    if ($this->isDynamicField($field)) {
      return $this->{$field};
    }
    return parent::val($field, $options);
  }

  /**
   * @inheritdoc
   */
  public function fieldNames(): array
  {
    $result = [];
    foreach(Language::getIds() as $language) {
      $result[] = $this->nameField($language);
      $result[] = $this->descriptionField($language);
    }
    return [...parent::fieldNames(), ...$result];
  }

  /**
   * @inheritdoc
   */
  public function type(string $field): ?string
  {
    if ($this->isDynamicField($field)) {
      return 'string';
    }
    return parent::type($field);
  }

  #endregion

  #region ModelBase

  /**
   * @inheritdoc
   */
  public function patch(array $data, bool $skipInvalid = false): bool
  {
    foreach(Language::getIds() as $language) {
      if (isset($data[$this->nameField($language)])) {
        $this->{$this->nameField($language)} = $data[$this->nameField($language)];
      }
      if (isset($data[$this->descriptionField($language)])) {
        $this->{$this->descriptionField($language)} = $data[$this->descriptionField($language)];
      }
    }
    return parent::patch($data, $skipInvalid);
  }

  #endregion

  #region public methods

  public function copyFromEntity(WorkshopEntity $entity): void
  {
    $this->id = $entity->id;
    $this->laptop = $entity->laptop;
    foreach (Language::getIds() as $languageId) {
      $this->{$this->nameField($languageId)} = $entity->getText(
        WorkshopTextEntity::NAME, $languageId
      );
      $this->{$this->descriptionField($languageId)} = $entity->getText(
        WorkshopTextEntity::DESCRIPTION, $languageId
      );
    }
    $this->setNew(false);
  }

  public function copyToEntity(WorkshopEntity $entity): void
  {
    $entity->laptop = $this->laptop;
    foreach (Language::getIds() as $languageId) {
      $entity->setText(
        WorkshopTextEntity::NAME, $this->{$this->nameField($languageId)}, $languageId
      );
      $entity->setText(
        WorkshopTextEntity::DESCRIPTION, $this->{$this->descriptionField($languageId)}, $languageId
      );
    }
  }

  public function clear(): void
  {
    parent::clear();
    $this->laptop = true;
    foreach (Language::getIds() as $languageId) {
      $this->{$this->nameField($languageId)} = '';
      $this->{$this->descriptionField($languageId)} = '';
    }
  }

  /**
   * Gets the field name for the name for a specific language.
   *
   * @param int $languageId
   *
   * @return string
   */
  public function nameField(int $languageId): string
  {
    return self::NAME_PREFIX.Language::getCode($languageId);
  }

  /**
   * Gets the field name for the description for a specific language.
   *
   * @param int $languageId
   *
   * @return string
   */
  public function descriptionField(int $languageId): string
  {
    return self::DESCRIPTION_PREFIX.Language::getCode($languageId);
  }

  /**
   * @inheritDoc
   */
  public function getFieldName(string $field): string
  {
    if (str_starts_with($field, self::NAME_PREFIX)) {
      return __('name');
    }
    if (str_starts_with($field, self::DESCRIPTION_PREFIX)) {
      return __('description');
    }
    return match ($field) {
      self::LAPTOP => __('laptop'),
      default => parent::getFieldName($field),
    };
  }

  #endregion

  #region private methods

  private function isDynamicField(string $fieldName): bool {
    return str_starts_with($fieldName, self::NAME_PREFIX) ||
      str_starts_with($fieldName, self::DESCRIPTION_PREFIX);
  }

  #endregion
}
