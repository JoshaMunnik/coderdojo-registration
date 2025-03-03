<?php

namespace App\Lib\Model\Base;

use Cake\Validation\Validator;
use DateTime;
use Exception;
use ReflectionClass;
use ReflectionProperty;

/**
 * {@link ModelBase} is a class that can be used to define a data class with validation support.
 *
 * Subclasses should define public properties and override {@link buildValidator}.
 */
class ModelBase
{
  #region private variables

  /**
   * Cached validator.
   *
   * @var Validator
   */
  private Validator $m_validator;

  /**
   * Errors from the latest validation.
   *
   * @var array
   */
  private array $m_errors = [];

  /**
   * Public properties
   *
   * @var ReflectionProperty[]
   */
  private array $m_properties;

  /**
   * When true the instance contains new data
   *
   * @var bool
   */
  private bool $m_new = true;

  #endregion

  #region public methods

  /**
   * Constructs an instance of {@link ModelBase}
   */
  public function __construct()
  {
    $classType = new ReflectionClass($this);
    $propertyList = $classType->getProperties(ReflectionProperty::IS_PUBLIC);
    $this->m_properties = [];
    foreach($propertyList as $property) {
      $this->m_properties[$property->getName()] = $property;
    }
  }

  /**
   * Updates the properties with values from an array. The key should match the name of the
   * properties.
   *
   * Before the data is assigned, the data is validated.
   *
   * @param array $data Data to patch instance with.
   * @param bool $skipInvalid True to skip invalid fields.
   *
   * @return bool Result of {@link isValid()}
   */
  public function patch(array $data, bool $skipInvalid = false): bool
  {
    $valid = $this->isValidData($data);
    foreach ($this->m_properties as $property) {
      $name = $property->getName();
      if (key_exists($name, $data) && (!key_exists($name, $this->m_errors) || !$skipInvalid)) {
        if ($property->getType()->getName() === DateTime::class) {
          try {
            $property->setValue($this, new DateTime($data[$property->getName()]));
          }
          catch (Exception $e) {
          }
        }
        else {
          $property->setValue($this, $data[$property->getName()]);
        }
      }
    }
    return $valid;
  }

  /**
   * Checks if the current property values in this instance are valid.
   *
   * @return bool True if properties are valid.
   */
  public function isValid(): bool
  {
    $data = [];
    foreach ($this->m_properties as $property) {
      $data[$property->getName()] = $property->getValue($this);
    }
    return $this->isValidData($data);
  }

  /**
   * Checks if the values in the array are valid.
   *
   * @param array $aData Data to validate
   *
   * @return bool True if the data is valid
   */
  public function isValidData(
    array $aData
  ): bool {
    $this->m_errors = $this->getValidator()->validate($aData, $this->isNew());
    return count($this->m_errors) == 0;
  }

  /**
   * Gets all errors from latest {@link isValid()} or {@link isValidData()} call.
   *
   * @return array Array of field names, each containing an array of error messages.
   */
  public function getAllErrors(): array
  {
    return $this->m_errors;
  }

  /**
   * Checks if the model has any errors.
   *
   * @return bool True if there are errors
   */
  public function hasErrors(): bool
  {
    return count($this->m_errors) > 0;
  }

  /**
   * Gets the errors for a specific field name.
   *
   * @param string $fieldName Field name to get error(s) for
   *
   * @return array A list of errors for the field.
   */
  public function getError(
    string $fieldName
  ): array {
    return key_exists($fieldName, $this->m_errors) ? $this->m_errors[$fieldName] : [];
  }

  /**
   * Indicates the data is new.
   *
   * @return bool True if the data is new
   */
  public function isNew(): bool
  {
    return $this->m_new;
  }

  /**
   * Returns the name for a field (for use in error messages and labels).
   *
   * The default implementation just returns the field name. Subclasses can override this method.
   *
   * @param string $field
   *
   * @return string
   */
  public function getFieldName(string $field): string
  {
    return $field;
  }

  /**
   * Clears some or all of the fields in the model.
   *
   * @return void
   */
  public function clear(): void
  {
  }

  #endregion

  #region protected methods

  /**
   * @return ReflectionProperty[]
   */
  protected function getProperties(): array
  {
    return $this->m_properties;
  }

  /**
   * @param string $name
   * @return ReflectionProperty
   */
  protected function getProperty(
    string $name
  ): ReflectionProperty {
    return $this->m_properties[$name];
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  protected function hasProperty(
    string $name
  ): bool {
    return key_exists($name, $this->m_properties);
  }

  /**
   * Updates the new state.
   *
   * @param bool $new New state
   *
   * @return void
   */
  protected function setNew(
    bool $new
  ): void {
    $this->m_new = $new;
  }

  /**
   * Returns a validator for the model class. This method calls {@link buildValidator} the first
   * time and caches the result.
   *
   * Subclasses should override the {@link buildValidator} method.
   *
   * @return Validator Validator to use
   */
  protected function getValidator(): Validator
  {
    if (empty($this->m_validator)) {
      $this->m_validator = $this->buildValidator();
    }
    return $this->m_validator;
  }

  /**
   * Builds a validator for the model.
   *
   * The default method just returns an empty {@link Validator} instance. Subclasses can
   * override this method to add rules for fields.
   *
   * @return Validator Validator instance
   */
  protected function buildValidator(): Validator
  {
    return new Validator();
  }

  #endregion
}
