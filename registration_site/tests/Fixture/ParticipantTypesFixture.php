<?php

namespace App\Test\Fixture;

use App\Model\Value\ParticipantType;
use Cake\TestSuite\Fixture\TestFixture;

class ParticipantTypesFixture extends TestFixture
{
  public string $table = 'cd_participant_types';

  public array $records = [
    [
      'id' => ParticipantType::CHILDREN,
      'name' => 'Children',
    ],
    [
      'id' => ParticipantType::ALL,
      'name' => 'All',
    ],
  ];
}
