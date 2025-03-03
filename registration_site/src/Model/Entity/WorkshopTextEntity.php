<?php

namespace App\Model\Entity;

use App\Lib\Model\Entity\IEntityWithId;
use App\Lib\Model\Entity\IEntityWithTimestamp;
use Cake\ORM\Entity;

/**
 * {@link WorkshopTextEntity} encapsulates a the texts for a workshop for a certain language.
 *
 * @property string $workshop_id
 * @property int $language_id - see {@link Language}
 * @property string $name
 * @property string $description
 *
 * @property WorkshopEntity $workshop
 */
class WorkshopTextEntity extends Entity implements IEntityWithTimestamp, IEntityWithId
{
  #region field constants

  public const WORKSHOP_ID = 'workshop_id';
  public const LANGUAGE_ID = 'language_id';
  public const NAME = 'name';
  public const DESCRIPTION = 'description';

  #endregion
}
