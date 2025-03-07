<?php

namespace App\View\Helper;

use App\Lib\Model\Base\ModelBase;
use App\Model\Enum\ButtonColorEnum;
use App\Model\Enum\ButtonIconEnum;
use App\Model\Enum\CellDataTypeEnum;
use App\Model\Enum\CellStylingEnum;
use App\Model\Enum\ContentPositionEnum;
use App\Model\Enum\GapEnum;
use App\Tool\UrlTool;
use Cake\View\Helper;
use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use DateTimeInterface;

/**
 * Styling helper can be used to create styled html elements.
 *
 * @property HtmlHelper $Html
 * @property FormHelper $Form
 */
class StylingHelper extends Helper
{
  #region configuration

  protected array $helpers = ['Html', 'Form'];

  #endregion

  #region private variables

  private string $m_tabId = '';

  #endregion

  #region public methods

  /**
   * @param string $title
   * @param string|array $url
   * @param ButtonColorEnum $color
   * @return string
   */
  public function linkButton(
    string $title,
    string|array $url,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY
  ): string {
    return $this->Html->link(
      $title,
      UrlTool::Url($url),
      [
        'class' => 'cd-button__normal '.$this->getButtonColorClass($color),
        'escape' => false,
      ]
    );
  }

  /**
   * @param string $title
   * @param string|array $url
   * @return string
   */
  public function linkText(string $title, string|array $url): string
  {
    return $this->Html->link(
      $title,
      UrlTool::Url($url),
      [
        'class' => 'cd-button__link',
        'escape' => false,
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function button(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param ButtonIconEnum $icon
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function iconButton(
    ButtonIconEnum $icon,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $this->getButtonIconHtml($icon),
      [
        'class' => 'cd-button__normal cd-button__normal--is-icon '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function bigButton(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal cd-button__normal--is-big '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param ButtonIconEnum $icon
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function bigIconButton(
    ButtonIconEnum $icon,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $this->getButtonIconHtml($icon),
      [
        'class' => 'cd-button__normal cd-button__normal--is-big cd-button__normal--is-icon '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @return string
   */
  public function staticButton(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::DISABLED,
  ): string {
    return $this->Html->tag(
      'div',
      $title,
      [
        'class' => 'cd-button__normal '.$this->getButtonColorClass($color),
        'escape' => false,
      ]
    );
  }

  /**
   * @param string $title
   * @param array $attributes
   * @return string
   */
  public function textButton(
    string $title,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__link',
        'type' => 'button',
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param string $name
   * @param array $attributes
   * @return string
   */
  public function submit(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    string $name = '',
    array $attributes = [],
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal '.$this->getButtonColorClass($color),
        'type' => 'submit',
        'escape' => false,
        'name' => $name,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param string $name
   * @param array $attributes
   * @return string
   */
  public function bigSubmit(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    string $name = '',
    array $attributes = [],
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal cd-button__normal--is-big '.$this->getButtonColorClass($color),
        'type' => 'submit',
        'escape' => false,
        'name' => $name,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function tableButton(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal cd-button__normal--is-table '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function tableStaticButton(
    string $title,
    ButtonColorEnum $color = ButtonColorEnum::DISABLED,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'div',
      $title,
      [
        'class' => 'cd-button__normal cd-button__normal--is-table '.$this->getButtonColorClass($color),
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param string $title
   * @param string|array $url
   * @param ButtonColorEnum $color
   * @return string
   */
  public function tableLinkButton(
    string $title,
    string|array $url,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY
  ): string {
    return $this->Html->link(
      $title,
      UrlTool::Url($url),
      [
        'class' => 'cd-button__normal cd-button__normal--is-table '.$this->getButtonColorClass($color),
        'escape' => false,
      ]
    );
  }

  /**
   * @param ButtonIconEnum $icon
   * @param ButtonColorEnum $color
   * @param array $attributes
   * @return string
   */
  public function tableIconButton(
    ButtonIconEnum $icon,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY,
    array $attributes = []
  ): string {
    return $this->Html->tag(
      'button',
      $this->GetButtonIconHtml($icon),
      [
        'class' => 'cd-button__normal cd-button__normal--is-icon cd-button__normal--is-table '.$this->getButtonColorClass($color),
        'type' => 'button',
        'escape' => false,
        ...$attributes
      ]
    );
  }

  /**
   * @param ButtonIconEnum $icon
   * @param string|array $url
   * @param ButtonColorEnum $color
   * @return string
   */
  public function tableLinkIconButton(
    ButtonIconEnum $icon,
    string|array $url,
    ButtonColorEnum $color = ButtonColorEnum::PRIMARY
  ): string {
    return $this->Html->link(
      $this->GetButtonIconHtml($icon),
      UrlTool::Url($url),
      [
        'class' => 'cd-button__normal cd-button__normal--is-icon cd-button__normal--is-table '.$this->getButtonColorClass($color),
        'escape' => false,
      ]
    );
  }

  /**
   * @param string $title
   * @return string
   */
  public function closeButton(string $title): string
  {
    return $this->Html->tag(
      'button',
      $title,
      [
        'class' => 'cd-button__normal cd-button__normal--is-secondary',
        'type' => 'button',
        'escape' => false,
        'data-uf-click-action' => 'close',
        'data-uf-click-target' => '_dialog'
      ]
    );
  }

  /**
   * @param bool $on
   * @param string $onLabel
   * @param string $offLabel
   * @param array $attributes
   * @return string
   */
  public function toggleButton(
    bool $on,
    string $onLabel,
    string $offLabel,
    array $attributes = []
  ): string {
    $html = '<label class="cd-form__toggle-container">';
    $html .= $this->Form->checkbox(
      '',
      [
        'checked' => $on,
        'class' => 'cd-form__toggle-checkbox',
        ...$attributes,
      ]
    );
    $html .= '<span class="cd-form__toggle-label-checked">'.$onLabel.'</span>';
    $html .= '<span class="cd-form__toggle-label-unchecked">'.$offLabel.'</span>';
    $html .= '<span class="cd-form__toggle-span"></span>';
    $html .= '</label>';
    return $html;
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function text(string $text, array $attributes = []): string
  {
    return $this->Html->tag('span', $text, ['class' => 'cd-text__normal', ...$attributes]);
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function successText(string $text, array $attributes = []): string
  {
    return $this->Html->tag(
      'span', $text, ['class' => 'cd-text__normal cd-text--is-success', ...$attributes]
    );
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function dangerText(string $text, array $attributes = []): string
  {
    return $this->Html->tag(
      'span', $text, ['class' => 'cd-text__normal cd-text--is-danger', ...$attributes]
    );
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function textBlock(string $text, array $attributes = []): string
  {
    return $this->Html->tag('p', $text, ['class' => 'cd-text__normal', ...$attributes]);
  }

  /**
   * @param string|null $title
   * @param string[] $items
   *
   * @return string
   */
  public function textList(string|null $title, array $items): string
  {
    $result = '<div>';
    if ($title) {
      $result .= $this->textBlock($title);
    }
    $result .= '<ul class="cd-list__container">';
    foreach ($items as $item) {
      $result .= '<li class="cd-list__item">'.$this->text($item).'</li>';
    }
    $result .= '</ul></div>';
    return $result;
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function strongTextBlock(string $text, array $attributes = []): string
  {
    return $this->Html->tag(
      'p',
      $text,
      ['class' => 'cd-text__normal cd-text--is-strong', ...$attributes]
    );
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function title(string $text, array $attributes = []): string
  {
    if (empty($this->getView()->fetch('title'))) {
      $this->getView()->assign('title', $text);
      return $this->Html->tag('h1', $text, ['class' => 'cd-text__title', ...$attributes]);
    }
    return $this->Html->tag('h2', $text, ['class' => 'cd-text__title', ...$attributes]);
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function smallTitle(string $text, array $attributes = []): string
  {
    return $this->Html->tag(
      'h3',
      $text,
      ['class' => 'cd-text__small-title', ...$attributes]
    );
  }

  /**
   * @param string $text
   * @param array $attributes
   * @return string
   */
  public function dialogTitle(string $text, array $attributes = []): string
  {
    return $this->Html->tag(
      'h3',
      $text,
      ['class' => 'cd-dialog__title cd-text__small-title', ...$attributes]
    );
  }

  /**
   * @param bool $checked
   * @return string
   */
  public function checkbox(bool $checked): string
  {
    return $checked ? $this->checkedCheckbox() : $this->uncheckedCheckbox();
  }

  /**
   * @param bool $show
   * @return string
   */
  public function checkedCheckbox(bool $show = true): string
  {
    return $show ? '<i class="fa-regular fa-check-square"></i>' : '';
  }

  /**
   * @param bool $show
   * @return string
   */
  public function uncheckedCheckbox(bool $show = true): string
  {
    return $show ? '<i class="fa-regular fa-square"></i>' : '';
  }

  /**
   * Renders the dialog, form, hidden input fields and content layout tags
   *
   * @param string $id Dom id of the dialog
   * @param string $title Title of dialog
   * @param ModelBase $data Data to post
   * @param array|null $url Url to post to or null to use current url
   * @param array $hiddenFields Either the name of the hidden field or name => data attribute to
   *  create an unlocked hidden input with a data attribute.
   *
   * @return string
   */
  public function beginFormDialog(
    string $id,
    string $title,
    ModelBase $data,
    array|null $url = null,
    array $hiddenFields = []

  ): string {
    $html = '<dialog id="'.$id.'" class="cd-dialog__container"';
    if ($data->hasErrors()) {
      $html .= ' data-uf-load-action="show-modal"';
      $html .= ' data-uf-event-action="reload"';
      $html .= ' data-uf-event-events="close"';
    }
    $html .= '>';
    $options = [
      'templates' => 'form_styles',
      'valueSources' => ['context'],
      'idPrefix' => basename(str_replace('\\', '/', get_class($data))),
    ];
    if ($url) {
      $options['url'] = UrlTool::url($url);
    }
    $html .= $this->Form->create($data, $options);
    foreach ($hiddenFields as $name => $value) {
      if (is_int($name)) {
        $html .= $this->Form->hidden($value);
      }
      else {
        $this->Form->unlockField($name);
        $html .= $this->Form->hidden($name, is_array($value) ? $value : [$value]);
      }
    }
    $html .= '<div class="cd-dialog__layout">';
    $html .= $this->dialogTitle($title);
    return $html;
  }

  /**
   * @return string
   */
  public function endFormDialog(): string
  {
    $html = '</div>';
    $html .= $this->Form->end();
    $html .= '</dialog>';
    return $html;
  }

  /**
   * @return string
   */
  public function beginFormContainer(): string
  {
    return '<div class="cd-form__container">';
  }

  /**
   * @return string
   */
  public function endFormContainer(): string
  {
    return '</div>';
  }

  /**
   * @return string
   */
  public function beginDialogButtons(): string
  {
    return '<div class="cd-dialog__buttons">';
  }

  /**
   * @return string
   */
  public function endDialogButtons(): string
  {
    return '</div>';
  }

  /**
   * @param GapEnum $gap
   * @return string
   */
  public function beginRow(GapEnum $gap = GapEnum::SMALL): string
  {
    $gapClass = match ($gap) {
      default => '',
      GapEnum::SMALL => ' cd-layout__row--has-small-gap',
      GapEnum::FORM => ' cd-layout__row--has-form-gap',
      GapEnum::DIALOG => ' cd-layout__row--has-dialog-gap',
    };
    return '<div class="cd-layout__row'.$gapClass.'">';
  }

  /**
   * @return string
   */
  public function endRow(): string
  {
    return '</div>';
  }

  /**
   * @param GapEnum $gap
   * @return string
   */
  public function beginColumn(GapEnum $gap = GapEnum::SMALL): string
  {
    $gapClass = match ($gap) {
      default => '',
      GapEnum::SMALL => ' cd-layout__column--has-small-gap',
      GapEnum::FORM => ' cd-layout__column--has-form-gap',
      GapEnum::DIALOG => ' cd-layout__column--has-dialog-gap',
    };
    return '<div class="cd-layout__column'.$gapClass.'">';
  }

  /**
   * @return string
   */
  public function endColumn(): string
  {
    return '</div>';
  }

  /**
   * @return string
   */
  public function beginFormButtons(): string
  {
    return '<div class="cd-form__buttons">';
  }

  /**
   * @return string
   */
  public function endFormButtons(): string
  {
    return '</div>';
  }

  /**
   * @param string|null $storageId
   * @param bool $fullWidth
   * @return string
   */
  public function beginSortedTable(string|null $storageId, bool $fullWidth = false): string
  {
    $html = '
    <table
     class="cd-table__container'.($fullWidth ? ' cd-table__container--is-full-width' : '').'"
     data-uf-sorting
     data-uf-sort-ascending="cd-table__cell--is-ascending"
     data-uf-sort-descending="cd-table__cell--is-descending"
     ';
    if ($storageId) {
      $html .= ' data-uf-storage-id="'.$storageId.'"';
    }
    $html .= '>';
    return $html;
  }

  /**
   * @return string
   */
  public function endSortedTable(): string
  {
    return '</table>';
  }

  /**
   * Creates a table header row with cell elements containing sorting buttons.
   *
   * Each entry in a column is a key and value pair. The key is the column name and the value
   * is either a {@link CellDataTypeEnum} or a {@link CellStylingEnum} or an array containing a
   * combination of those values.
   *
   * If the value is a null value, the header is rendered as actions column that can not be sorted
   * upon.
   *
   * @param array $columns See comments.
   *
   * @return string
   */
  public function sortedTableHeader(array $columns): string
  {
    $html = '<tr class="cd-table__row cd-table__row--is-header" data-uf-header-row>';
    foreach ($columns as $key => $value) {
      if ($value == null) {
        $html .= '<th class="cd-table__cell cd-table__cell--is-header cd-table__cell--is-tight">
          &nbsp;
        </th>
      ';
      }
      else {
        if (!is_array($value)) {
          $value = [$value];
        }
        $sortType = $this->getCellSortType($value);
        $classes = $this->getCellStylingClasses($value);
        $html .= '<th class="cd-table__cell cd-table__cell--is-header '.$classes.'" '.$sortType.'>';
        $html .= '<button class="cd-button__table-header">'.$key.'</button>';
        $html .= '</th>';
      }
    }
    $html .= '</tr>';
    return $html;
  }

  /**
   * The columns either contains a value entry or key and value pair. With a key and value pair,
   * the value contains the position and attributes and the key the content.
   * Else the value is the content.
   *
   * If the content is not unique, wrap it in an array with one element. The element inside can
   * again be a content value or a key and value pair where the key is the content and the value
   * a position and attributes.
   *
   * The position and attributes is either a single value which should be a value of the type
   * {@link ContentPositionEnum} or an array with the position and additional attributes added
   * to the cell tag.
   *
   * If the content is a {@link DateTimeInterface} the date is formatted as 'Y-m-d H:i'.
   *
   * @param array $columns See comment.
   * @param array $buttons When not empty, add a table column with buttons.
   * @param bool $isActive True to add active state to the row and columns.
   *
   * @return string
   */
  public function sortedTableRow(
    array $columns,
    array $buttons = [],
    bool $isActive = false
  ): string {
    $activeRow = $isActive ? ' cd-table__row--is-active' : '';
    $html = '<tr class="cd-table__row cd-table__row--is-data'.$activeRow.'">';
    foreach ($columns as $key => $value) {
      list($key, $value) = $this->checkTableCellEntry($key, $value);
      list($position, $attributes) = $this->checkTableCellValue($value);
      $cssClasses = 'cd-table__cell cd-table__cell--is-data';
      $cssClasses .= $this->getCellStylingClasses(is_array($value) ? $value : [$value]);
      if ($key instanceof DateTimeInterface) {
        $cssClasses .= ' cd-table__cell--is-date';
      }
      if ($isActive) {
        $cssClasses .= ' cd-table__cell--is-active';
      }
      $cssClasses .= match ($position) {
        ContentPositionEnum::CENTER => ' cd-table__cell--at-center',
        ContentPositionEnum::END => ' cd-table__cell--at-right',
        default => '',
      };
      $attributes = ' '.$this->Html->templater()->formatAttributes($attributes);
      $html .= '<td class="'.$cssClasses.'"'.$attributes.'>';
      if ($key instanceof DateTimeInterface) {
        $html .= str_replace(' ', '&nbsp;', $key->format('Y-m-d H:i'));
      }
      else {
        $html .= $key;
      }
      $html .= '</td>';
    }
    if (!empty($buttons)) {
      $html .= '<td class="cd-table__cell cd-table__cell--is-data">';
      $html .= $this->beginButtons();
      foreach ($buttons as $button) {
        $html .= $button;
      }
      $html .= $this->endButtons();
      $html .= '</td>';
    }
    $html .= '</tr>';
    return $html;
  }

  /**
   * @return string
   */
  public function beginPageButtons(): string
  {
    return '<nav class="cd-layout__buttons cd-layout__buttons--wrap">';
  }

  /**
   * @return string
   */
  public function endPageButtons(): string
  {
    return '</nav>';
  }

  /**
   * @return string
   */
  public function beginButtons(): string
  {
    return '<div class="cd-layout__buttons">';
  }

  /**
   * @return string
   */
  public function endButtons(): string
  {
    return '</div>';
  }

  /**
   * @return string
   */
  public function beginTabsContainer(): string {
    $this->m_tabId = uniqid('cd-tabs__container-');
    return '<div class="cd-tabs__container">';
  }

  /**
   * @return string
   */
  public function endTabsContainer(): string {
    return '</div>';
  }

  /**
   * This method should be called after a call to {@link beginTabsContainer()}.
   *
   * @param string $title
   * @param bool $selected
   * @return string
   */
  public function beginTab(string $title, bool $selected = false): string {
    $id = uniqid('cd-tabs__tab-');
    $html = '<input type="radio" id="'.$id.'" name="'.$this->m_tabId.'" class="cd-tabs__tab-radio" '
      .($selected ? ' checked' : '')
      .' />';
    $html .= '<label class="cd-tabs__title" for="'.$id.'">'.$title.'</label>';
    $html .= '<div class="cd-tabs__content">';
    return $html;
  }

  /**
   * @return string
   */
  public function endTab(): string {
    return '</div>';
  }

  #endregion

  #region private methods

  /**
   * @param ButtonColorEnum $color
   * @return string
   */
  private function getButtonColorClass(ButtonColorEnum $color): string
  {
    return match ($color) {
      ButtonColorEnum::PRIMARY => 'cd-button__normal--is-primary',
      ButtonColorEnum::SECONDARY => 'cd-button__normal--is-secondary',
      ButtonColorEnum::TERTIARY => 'cd-button__normal--is-tertiary',
      ButtonColorEnum::SUCCESS => 'cd-button__normal--is-success',
      ButtonColorEnum::DANGER => 'cd-button__normal--is-danger',
      ButtonColorEnum::WARNING => 'cd-button__normal--is-warning',
      ButtonColorEnum::DISABLED => 'cd-button__normal--is-disabled',
    };
  }

  /**
   * @param ButtonIconEnum $icon
   * @return string
   */
  private function getButtonIconHtml(ButtonIconEnum $icon): string
  {
    return match ($icon) {
      ButtonIconEnum::EDIT => '<i class="fas fa-pen"></i>',
      ButtonIconEnum::REMOVE => '<i class="fas fa-trash-can"></i>',
      ButtonIconEnum::PARTICIPANTS => '<i class="fas fa-users"></i>',
      ButtonIconEnum::WORKSHOP => '<i class="fas fa-computer"></i>',
      default => '',
    };
  }

  /**
   * @param int|string $key
   * @param mixed $value
   * @return array
   */
  private function checkContentPosition(int|string $key, mixed $value): array
  {
    if (is_int($key) && !($value instanceof ContentPositionEnum)) {
      $key = $value;
      $value = ContentPositionEnum::START;
    }
    return array($key, $value);
  }

  /**
   * @param mixed $key
   * @param mixed $value
   * @return array
   */
  private function checkTableCellEntry(mixed $key, mixed $value): array
  {
    list($key, $value) = $this->checkContentPosition($key, $value);
    if (is_array($key)) {
      reset($key);
      $value = current($key);
      $key = key($key);
      list($key, $value) = $this->checkContentPosition($key, $value);
    }
    return array($key, $value);
  }

  /**
   * @param mixed $cellValue
   * @return array
   */
  private function checkTableCellValue(mixed $cellValue): array
  {
    if (!is_array($cellValue)) {
      return array($cellValue, []);
    }
    $attributes = [];
    $position = ContentPositionEnum::START;
    foreach ($cellValue as $key => $value) {
      if ($value instanceof ContentPositionEnum) {
        $position = $value;
      }
      else {
        $attributes[$key] = $value;
      }
    }
    return [$position, $attributes];
  }

  /**
   * Checks if any of the array values contains a {@link CellDataTypeEnum} and returns the
   * corresponding attribute definition.
   *
   * @param array $cellValues
   *
   * @return string Attribute definition or empty string if no sort type could be determined.
   */
  private function getCellSortType(array $cellValues): string
  {
    foreach ($cellValues as $cellValue) {
      if ($cellValue instanceof CellDataTypeEnum) {
        return match ($cellValue) {
          CellDataTypeEnum::DATE => ' data-uf-sort-type="date"',
          CellDataTypeEnum::NUMBER => ' data-uf-sort-type="number"',
          default => ' data-uf-sort-type="text"',
        };
      }
    }
    return '';
  }

  /**
   * Processes the array values and check for {@link CellStylingEnum} entries. Returns the
   * css classes for the request styling.
   *
   * @param array $cellValues
   *
   * @return string Css classes or empty string if there was no styling value.
   */
  private function getCellStylingClasses(array $cellValues): string
  {
    $result = '';
    foreach ($cellValues as $cellValue) {
      if ($cellValue instanceof CellStylingEnum) {
        $result .= match ($cellValue) {
          CellStylingEnum::TIGHT => ' cd-table__cell--is-tight',
          CellStylingEnum::DATE => ' cd-table__cell--is-date',
          default => '',
        };
      }
    }
    return $result;
  }

  #endregion
}
