<?php

use Kirby\CMS\API;
use Kirby\CMS\Blueprint;
use Kirby\Cms\Section;
use Kirby\Toolkit\A;


class CustomAddDialog {

  protected $id;
  protected $api;
  protected $parent;
  protected $dialog;
  protected $hasForcedTemplate;
  protected $forcedTemplate;

  public function __construct(string $id = '', API $api) {
    $this->id = $id;
    $this->api = $api;
    $this->parent = ($id == '') ? $api->site() : $api->page($id);

    $this->dialog = new AddDialog($this->parent->blueprint()->name());

    $forcedTemplateFieldName = option('steirico.kirby-plugin-custom-add-fields.forcedTemplate.fieldName');
    $forcedTemplateFieldName = $forcedTemplateFieldName ? $forcedTemplateFieldName : '';
    $this->hasForcedTemplate = $this->parent->content()->has($forcedTemplateFieldName);
    $this->forcedTemplate = '';
    
    if ($this->hasForcedTemplate) {
      $this->forcedTemplate = $this->parent->{$forcedTemplateFieldName}()->value();
    }

    $this->forcedTemplate = $this->forcedTemplate != '' ? $this->forcedTemplate : $this->dialog->forcedTemplate();
  }

  protected function getSectionTemplateNames() {
    $templates = $this->parent->blueprints($this->api->requestQuery('section'));
    return array_map(function($template){
      return new Template($template);
    }, $templates);
  }

  protected function getSkipDialogData() {
    if ($this->forcedTemplate == '') {
      throw new Exception("Set 'forcedTemplate' in order to skip add dialog.");
    } else {
      $dialog = new AddDialog($this->forcedTemplate);
      return $dialog->skipDialogData($this->dialog);
    }
  }

  public function getAddFieldsData() {
    $result = [];
    
    if ($this->dialog->skip()) {
      return $this->getSkipDialogData();
    }

    $templates = $this->getSectionTemplateNames();

    foreach ($templates as $template) {
      if ($this->hasForcedTemplate && $template->name() != $this->forcedTemplate) {
        continue;
      }
      try {
        $addFields = $template->dialog()->fields();
 
        if ($addFields) {
          $dialogProperties = A::get($addFields, '__dialog', null);
          if ($dialogProperties) {
            $redirectToNewPage = A::get($addFields['__dialog'], 'redirect', false);
            unset($addFields['__dialog']);
          } else {
            $redirectToNewPage = false;
          }


          if (!empty($addFields)) {
            $fieldOrder = array_change_key_case($addFields, CASE_LOWER);

            $title = A::get($addFields, 'title', null);
            if ($title) {
              $addFields["kirby-plugin-custom-add-fields-title"] = $title;
            }
            $attr = [
              'model' => $this->parent,
              'fields' => $addFields
            ];
            $addSection = new Section('fields', $attr);
            $addFields = $addSection->fields();

            if ($title) {
              $addFields['title'] = $addFields["kirby-plugin-custom-add-fields-title"];
              unset($addFields["kirby-plugin-custom-add-fields-title"]);
              $addFields['title']['name'] = 'title';
            }

            $addFields = array_replace($fieldOrder, $addFields);
          }
        } else {
          $redirectToNewPage = true;
        }
        array_push($result, [
          'name'  => $template->name(),
          'title' => $template->title(),
          'addFields' => $addFields,
          'options' => [
            'redirectToNewPage' => $redirectToNewPage
          ],
          'parentPage' => $this->id
        ]);
      } catch (Throwable $e) {
      }
    }
    return $result;
  }
}
