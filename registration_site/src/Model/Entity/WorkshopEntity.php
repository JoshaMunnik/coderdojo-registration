<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use App\Model\Value\Language;
use Cake\I18n\I18n;
use Cake\ORM\Entity;

/**
 * {@link WorkshopEntity} encapsulates a workshop in the database.
 * *
 * @property bool $laptop
 *
 * @property WorkshopTextEntity[] $workshop_texts
 */
class WorkshopEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const LAPTOP = 'laptop';
  public const WORKSHOP_TEXTS = 'workshop_texts';

  #endregion

  #region public methods

  /**
   * Gets the name of the workshop for the current locale.
   *
   * @param int|null $languageId
   * @return string
   */
  public function getName(int $languageId = null): string
  {
    return $this->getText(WorkshopTextEntity::NAME, $languageId);
  }

  /**
   * Gets the description of the workshop for the current locale.
   *
   * @param int|null $languageId
   * @return string
   */
  public function getDescription(int $languageId = null): string
  {
    return $this->getText(WorkshopTextEntity::DESCRIPTION, $languageId);
  }

  /**
   * Gets the text for a certain field (see {@link WorkshopTextEntity}).
   *
   * When the language is set, the method tries to get the text for the specified language.
   *
   * Else the method tries to get the text for the current locale; if that fails the method tries
   * to get the text for the default locale. If no text can be found, the method returns an
   * empty string.
   *
   * @param string $field
   * @param int|null $languageId When set, only get text for the specified language id.
   *
   * @return string
   */
  public function getText(string $field, ?int $languageId = null): string
  {
    if ($languageId) {
      return $this->getTextForLanguage($field, $languageId);
    }
    $languageId = Language::getId(I18n::getLocale());
    $defaultLanguageId = Language::getId(I18n::getDefaultLocale());
    return $this->getTextForLanguage($field, $languageId, $defaultLanguageId);
  }

  /**
   * Sets the text for a certain field (see {@link WorkshopTextEntity}) and language.
   *
   * @param string $field
   * @param string $value
   * @param int $languageId
   *
   * @return void
   */
  public function setText(string $field, string $value, int $languageId): void
  {
    $textEntity = $this->findTextEntity($languageId);
    if (!$textEntity) {
      $textEntity = new WorkshopTextEntity();
      $textEntity->language_id = $languageId;
      if (!isset($this->workshop_texts)) {
        $this->workshop_texts = [];
      }
      $this->workshop_texts[] = $textEntity;
    }
    $textEntity->set($field, $value);
    // process also the array when saving the entity
    $this->setDirty(self::WORKSHOP_TEXTS);
  }

  #region private methods

  /**
   * Tries to get the text for a certain field (see {@link WorkshopTextEntity}) and language.
   *
   * @param string $field
   * @param int $languageId
   * @param int|null $defaultLanguageId
   *
   * @return string
   */
  private function getTextForLanguage(
    string $field,
    int $languageId,
    ?int $defaultLanguageId = null
  ): string {
    $textEntity = $this->findTextEntity($languageId);
    $result = $textEntity ? $textEntity->get($field) : '';
    if (!empty($result)) {
      return $result;
    }
    if (!$defaultLanguageId) {
      return '';
    }
    $textEntity = $this->findTextEntity($defaultLanguageId);
    return $textEntity ? $textEntity->get($field) : '';
  }

  /**
   * Returns an entity for a certain language id.
   *
   * @param int $languageId
   *
   * @return WorkshopTextEntity|null
   */
  private function findTextEntity(int $languageId): ?WorkshopTextEntity
  {
    if (!isset($this->workshop_texts)) {
      return null;
    }
    foreach ($this->workshop_texts as $text) {
      if ($text->language_id === $languageId) {
        return $text;
      }
    }
    return null;
  }

  #endregion
}
