<?php

namespace App\Model\Table;

use App\Lib\Model\Table\TableWithTimestamp;
use App\Model\Entity\EventEntity;
use App\Model\Entity\WorkshopTextEntity;

/**
 * This table contains the texts for the workshops for a certain language.
 *
 * @property WorkshopsTable $Workshops
 */
class WorkshopTextsTable extends TableWithTimestamp
{
  #region cakephp callbacks

  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setEntityClass(WorkshopTextEntity::class);
    $this
      ->belongsTo(WorkshopsTable::getDefaultAlias())
      ->setForeignKey(WorkshopTextEntity::WORKSHOP_ID);
  }

  #endregion
}
