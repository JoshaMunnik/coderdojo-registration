<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\WorkshopEntity;
use App\Model\Entity\WorkshopTextEntity;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query;

/**
 * The workshops that can be added to events.
 *
 * All queries will return the related texts.
 *
 * @property WorkshopTextsTable $WorkshopTexts
 * @property EventWorkshopsTable $EventWorkshops
 */
class WorkshopsTable extends TableWithTimestamp
{
  #region cakephp callbacks

  /**
   * @inheritDoc
   */
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(WorkshopEntity::class);
    $this
      ->hasMany(WorkshopTextsTable::getDefaultAlias())
      ->setForeignKey(WorkshopTextEntity::WORKSHOP_ID);
    $this->hasMany(EventWorkshopsTable::getDefaultAlias());
  }

  /**
   * @inheritDoc
   */
  public function beforeFind(
    EventInterface $event,
    Query $query,
    ArrayObject $options,
    $primary
  ): void {
    $query->contain(WorkshopTextsTable::getDefaultAlias());
  }

  #endregion

  #region public methods

  /**
   * @return WorkshopEntity[]
   */
  public function getAll(): array
  {
    return $this
      ->find('all')
      ->all()
      ->toList();
  }

  public function getForId(string $id): WorkshopEntity
  {
    /** @var WorkshopEntity $result */
    $result = $this->get($id);
    return $result;
  }

  #endregion
}
