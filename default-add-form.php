<?php

return function($page, $selectedTemplate) {

  $templates = $page->blueprint()->pages()->template();
  $hasForcedTemplate = $page->content()->has('forcedTemplate');
  $options = [];
  $templateFields = [];

  if($hasForcedTemplate){
    $selectedTemplate = $page->forcedTemplate()->value();
  }
  
  foreach($templates as $template) {
    $options[$template->name()] = $template->title();
    
    if(empty($selectedTemplate)){
      $selectedTemplate = $template->name();
    }

    if($template->name() == $selectedTemplate && array_key_exists("add-fields", $template->yaml) &&  is_array($template->yaml["add-fields"])){
      $templateFields = $template->yaml["add-fields"];
    }
  }

  $readOnly = $hasForcedTemplate || (count($options) == 1);

  $formFields = array(
    'template' => array(
      'label'    => 'Add a new page based on this template',
      'type'     => 'select',
      'options'  => $options,
      'default'  => $selectedTemplate,
      'required' => true,
      'readonly' => $readOnly,
      'icon'     => $readOnly ? $templates->first()->icon() : 'chevron-down',
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
