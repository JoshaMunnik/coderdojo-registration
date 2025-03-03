<?php

namespace App\Lib\Model\View;

use App\Lib\Model\Base\ModelBase;
use Cake\View\Form\ContextInterface;
use Cake\View\Helper\FormHelper;

/**
 * {@link ViewModelBase} extends {@link ModelBase} and implements the {@link ContextInterface} so
 * that it can be used with {@link FormHelper}.
 */
class ViewModelBase extends ModelBase implements ContextInterface
{
  #region ContextInterface

  /**
   * @inheritDoc
   */
  public function getPrimaryKey(): array
  {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function isPrimaryKey(string $field): bool
  {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function isCreate(): bool
  {
    return $this->isNew();
  }

  /**
   * @inheritDoc
   */
  public function val(string $field, array $options = []): mixed
  {
    return $this->hasProperty($field)
      ? $this->getProperty($field)->getValue($this)
      : $options['default'];
  }

  /**
   * @inheritDoc
   */
  public function isRequired(string $field): ?bool
  {
    return $this->getValidator()->isPresenceRequired($field, true);
  }

  /**
   * @inheritDoc
   */
  public function getRequiredMessage(string $field): ?string
  {
    return __('The {0} is required', $this->getFieldName($field));
  }

  /**
   * @inheritDoc
   */
  public function getMaxLength(string $field): ?int
  {
    $ruleSet = $this->getValidator()->field($field);
    $maxLength = $ruleSet->rule('maxLength');
    if (!isset($maxLength)) {
      return null;
    }
    return $maxLength->get('maxLength');
  }

  /**
   * @inheritDoc
   */
  public function fieldNames(): array
  {
    return array_keys($this->getProperties());
  }

  /**
   * @inheritDoc
   */
  public function type(string $field): ?string
  {
    return $this->hasProperty($field) ? $this->getProperty($field)->getType()->getName() : null;
  }

  /**
   * @inheritDoc
   */
  public function attributes(string $field): array
  {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function hasError(string $field): bool
  {
    return count($this->getError($field)) > 0;
  }

  /**
   * @inheritDoc
   */
  public function error(string $field): array
  {
    return $this->getError($field);
  }

  #endregion

}
