<?php

namespace App\Lib\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Utility\Text;
use DateTimeInterface;

/**
 * {@link TableWithTimestamp} adds the {@link TimestampBehavior} to a {@link Table}.
 *
 * @method DateTimeInterface timestamp(?DateTimeInterface $ts = null, bool $refreshTimestamp = false) Get or set the timestamp to be used
 * @method bool touch(EntityInterface $entity, string $eventName = 'Model.beforeSave') Touch an entity
 */
class TableWithTimestamp extends Table
{
  #region cakephp callbacks

  /**
   * @inheritDoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setTable('cd_'.$this->getTable());
    $this->addBehavior('Timestamp');
  }

  /**
   * Makes sure the id is set to a new uuid when saving a new entity.
   *
   * @param EventInterface $event
   * @param EntityInterface $entity
   * @param ArrayObject $options
   *
   * @return bool
   */
  public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): bool
  {
    if ($entity->isNew()) {
      // all entities use uuid ids, generate a new one when saving a new entity
      $entity->set('id', Text::uuid());
    }
    // continue saving
    return true;
  }

  #endregion

  #region public methods

  /**
   * Gets the default alias based on the class name removing the 'Table' text at the end.
   *
   * Code is based on {@link Table::getAlias()}
   *
   * @return string|null
   */
  public static function getDefaultAlias(): ?string
  {
    $alias = namespaceSplit(static::class);
    return substr(end($alias), 0, -5) ?: null;
  }

  /**
   * Adds the table alias to a column name using '.' as separater character.
   *
   * @param string $aColumnName
   *
   * @return string Column name with table alias
   */
  public function prefix(string $aColumnName): string
  {
    return $this->getAlias().'.'.$aColumnName;
  }

  #endregion

}
