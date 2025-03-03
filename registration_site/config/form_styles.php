<?php

/**
 * Form styles for forms used within the event related pages
 */

return [
  'button' => '<button class="cd-button__normal cd-button__normal--is-primary"{{attrs}}>{{text}}</button>',
  'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}" class="cd-form__checkbox"{{attrs}} /></span>',
  'error' => '<div class="cd-form__error"{{attrs}}>{{content}}</div>',
  'file' => '<input type="file" name="{{name}}" class="" {{attrs}}>',
  'formStart' => '<form {{attrs}}>',
  'formEnd' => '</form>',
  'input' => '<input type="{{type}}" name="{{name}}" class="cd-form__single-line {{inputClass}}"{{attrs}}/>',
  'label' => '<label class="cd-form__label"{{attrs}} >{{text}}</label>',
  'inputContainer' => '<div class="cd-form__input-container {{containerClass}}"{{attrs}}>{{content}}{{extraContent}}</div>',
  'inputContainerError' => '<div class="cd-form__input-container {{containerClass}}"{{attrs}}>{{content}}{{extraContent}}{{error}}</div>',
  'inputSubmit' => '<input type="{{type}}" class="cd-button__normal cd-button__normal--is-primary"{{attrs}}/>',
  'nestingLabel' => '{{hidden}}<label class="cd-form__nesting-label-container"{{attrs}}>{{input}}<span class="cd-form__nesting-label-text">{{text}}</span></label>',
  'option' => '<option value="{{value}}"{{attrs}}>{{text}}</option>',
  'optgroup' => '<optgroup label="{{label}}"{{attrs}}>{{content}}</optgroup>',
  'radio' => '<input type="radio" name="{{name}}" value="{{value}}" class="cd-form__radio"{{attrs}} /><span class="cd-form__radio-circle"></span>',
  'select' => '<select name="{{name}}" class="cd-form__drop-down {{inputClass}}"{{attrs}}>{{content}}</select>',
  'selectMultiple' => '<select name="{{name}}[]" multiple="multiple" class="cd-form__drop-down"{{attrs}}>{{content}}</select>',
  'submitContainer' => '{{content}}',
  'textarea' => '<textarea name="{{name}}" class="cd-form__multi-line"{{attrs}}>{{value}}</textarea>',
];
