<?php

namespace App\Test\Fixture;

use App\Model\Value\Language;
use Cake\TestSuite\Fixture\TestFixture;

class LanguagesFixture extends TestFixture
{
  public string $table = 'cd_languages';

  public array $records = [
    [
      'id' => Language::ENGLISH_ID,
      'name' => Language::ENGLISH_CODE,
    ],
    [
      'id' => Language::DUTCH_ID,
      'name' => Language::DUTCH_CODE,
    ],
  ];
}
