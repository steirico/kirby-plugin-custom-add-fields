<?php

return function($page, $selectedTemplate) {

  $options = [];
  $templateFields = [];

  foreach($page->blueprint()->pages()->template() as $template) {
    $options[$template->name()] = $template->title();
    
    if(empty($selectedTemplate)){
      $selectedTemplate = $template->name();
    }

    if($template->name() == $selectedTemplate && array_key_exists("add-fields", $template->yaml) &&  is_array($template->yaml["add-fields"])){
      $templateFields = $template->yaml["add-fields"];
    }
  }

  $formFields = array(
    'template' => array(
      'label'    => 'Add a new page based on this template',
      'type'     => 'select',
      'options'  => $options,
      'default'  => $selectedTemplate,
      'required' => true,
      'readonly' => count($options) == 1 ? true : false,
      'icon'     => count($options) == 1 ? $page->blueprint()->pages()->template()->first()->icon() : 'chevron-down',
    )
  );

  if(empty($templateFields) || !is_array($templateFields)){
    $templateFields = yaml::read(__DIR__ . DS . 'default-add-fields.yaml');
  }

  $formFields = array_merge($formFields, $templateFields);

  $form = new Kirby\Panel\Form($formFields);

  $form->cancel($page->isSite() ? '/' : $page);

  $form->buttons->submit->val(l('add'));

  return $form;

};
