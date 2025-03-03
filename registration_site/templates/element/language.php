<?php

use App\Tool\UrlTool;
use App\View\ApplicationView;
use Cake\I18n\I18n;
use Cake\Routing\Router;

/**
 * @var ApplicationView $this
 */

$language = I18n::getLocale() ?? 'en';
$url = Router::Url([
  UrlTool::CONTROLLER => $this->request->getParam(UrlTool::CONTROLLER),
  UrlTool::ACTION => $this->request->getParam(UrlTool::ACTION),
  UrlTool::LANGUAGE => 'xx',
  ...$this->request->getParam('pass')
]);
$url = str_replace('xx', '$value$', $url);
?>
<div class="cd-language__container">
  <?= $this->Form->select(
    'language',
    [
      'en' => 'English',
      'nl' => 'Nederlands',
    ],
    [
      'value' => $language,
      'data-uf-select-url' => $url,
      'class' => 'cd-language__select'
    ]
  ) ?>
</div>
