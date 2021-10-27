<?php

namespace Steineri\CustomAddFields;

use Kirby\Cms\Blueprint;
use Kirby\Cms\Section;
use Kirby\Cms\Find;
use Kirby\Exception\Exception;
use Kirby\Toolkit\A;
use Kirby\Panel\Field;
use Throwable;

class Plugin {
    static public function loadPageCreate() {
        // taken from src/kirby/config/areas/site/dialogs.php
        // the parent model for the new page
        $parent = get('parent', 'site');

        // the view on which the add button is located
        // this is important to find the right section
        // and provide the correct templates for the new page
        $view = get('view', $parent);

        // templates will be fetched depending on the
        // section settings in the blueprint
        $section = get('section');

        // this is the parent instance
        $object = Find::parent($parent);

        // this is the view model
        // i.e. site if the add button is on
        // the dashboard
        $view = Find::parent($view);
        
        $templates = $view->blueprints($section);

        $parentProps = Blueprint::load($object->blueprint()->name());
        $parentAddFields = A::get($parentProps, 'addFields', null);

        $dialogProperties = A::get($parentAddFields, '__dialog', null);
        $skipDialog  = A::get($dialogProperties, 'skip', false);

        $forcedTemplateFieldName = option('steirico.kirby-plugin-custom-add-fields.forcedTemplate.fieldName');
        $forcedTemplateFieldName = $forcedTemplateFieldName ? $forcedTemplateFieldName : '';
        $hasForcedTemplate = $object->content()->has($forcedTemplateFieldName);
        $forcedTemplate = '';

        if($hasForcedTemplate){
            $forcedTemplate = $object->{$forcedTemplateFieldName}()->value();
        }

        $forcedTemplate = $forcedTemplate != '' ? $forcedTemplate : A::get($dialogProperties, 'forcedTemplate', '');

        if ($skipDialog) {
            if ($forcedTemplate == ''){
                throw new Exception("Set 'forcedTemplate' in order to skip add dialog.");
            } else {
                $now = time();
                return [
                    'component' => 'k-page-create-dialog',
                    'props' => [
                        'options' => [
                            'skip' => true
                        ],
                        'fields' => [],
                        'submitButton' => false,
                        'cancelButton' => false,
                        'templateData' => [],
                        'value' => [
                            'parent'   => $parent,
                            'template' => $forcedTemplate,
                            'title' => $now,
                            'slug' => $now
                        ]
                    ]
                ];
            }
        }

        $forceTemplateSelection = option('steirico.kirby-plugin-custom-add-fields.forceTemplateSelectionField');
        if(!is_bool($forceTemplateSelection)) {
            $version = preg_replace('/.*(\d+\.\d+\.\d+).*/m', '$1', kirby()->version());
            $forceTemplateSelection = version_compare($version, '3.5.0', '<');
        }

        $templateSelectField = [];
        if ($forceTemplateSelection || count($templates) > 1 || option('debug') === true) {
            $templateSelectField = Field::template($templates, [
                'required' => true
            ]);
        } else {
            $templateSelectField = Field::hidden();
        }

        $templateData = array();
        $firstTemplate = '';

        foreach ($templates as $template) {
            if($hasForcedTemplate && $template['name'] != $forcedTemplate){
                continue;
            }
            try {
                $props = Blueprint::load('pages/' . $template['name']);
                $addFields = A::get($props, 'addFields', null);
                unset($addFields['__dialog']);

                if(empty($addFields)) {
                    $addFields = Blueprint::load("fields/default-add-fields");
                    $addFields = A::get($addFields, 'fields', null);
                }

                $fieldProps = Blueprint::fieldsProps($addFields);
                $fieldOrder = array_change_key_case($fieldProps, CASE_LOWER);
                $title = A::get($fieldProps, 'title', null);
                if($title) {
                    $fieldProps["kirby-plugin-custom-add-fields-title"] = $title;
                }
                $attr = [
                    'model' => $object,
                    'fields' => $fieldProps
                ];
                $addSection = new Section('fields', $attr);
                $addFields = $addSection->fields();

                if($title){
                    $addFields['title'] = $addFields["kirby-plugin-custom-add-fields-title"];
                    unset($addFields["kirby-plugin-custom-add-fields-title"]);
                    $addFields['title']['name'] = 'title';
                }

                $addFields = array_replace($fieldOrder, $addFields);
                $addFields['template'] = $templateSelectField;
                $addFields['parent'] = Field::hidden();

                $templateName = $template['name'];
                
                foreach($addFields as $name => $addField) {
                    // TODO POC Setting endpoints
                    $addFields[$name]['endpoints'] = [
                        'field' =>  $parent . "/addfields/" . $templateName . "/" . $name,
                        'section' => $parent . "/addsections/" . $templateName . "/" . $section,
                        'model' => $parent
                    ];

                    if($name == 'slug') {
                        $addFields[$name]['path'] = empty($object->id()) === false ? '/' . $object->id() . '/' : '/';
                    }
                }

                $firstTemplate = $firstTemplate == '' ? $templateName : $firstTemplate;
                $templateData[$templateName] = $addFields;
            } catch (Throwable $e) {}
        }
        return [
            'component' => 'k-page-create-dialog',
            'props' => [
                'fields' => $templateData[$firstTemplate],
                'submitButton' => t('page.draft.create'),
                'templateData' => $templateData,
                'value' => [
                    'parent'   => $parent,
                    'template' => $firstTemplate,
                    'title' => "",
                    'slug' => ""
                ]
            ]
        ];
    }

    static public function submitPageCreate($content) {
        // Prepare content
        $slug = $content['slug'];
        $slug = $slug == '' ? time() : $slug;
        $template = $content['template'];

        unset($content['slug']);
        unset($content['template']);

        // Add Page
        $parent = Find::parent(get('parent', 'site'));
        $page = $parent->createChild([
            'content'  => $content,
            'slug'     => $slug,
            'template' => $template,
        ]);

        // Evaluate redirect
        $props = $page->blueprint()->toArray();
        $addFields = A::get($props, 'addFields', null);
        $dialogProperties = A::get($addFields, '__dialog', null);
        $redirectTarget = $parent->panel()->url(true);
        if($dialogProperties) {
            $redirectConfig = A::get($addFields['__dialog'], 'redirect', false);
            if(is_string($redirectConfig)){
                $redirectTarget = kirby()->page($redirectConfig)->panel()->url(true);
            } else if($redirectConfig == true){
                $redirectTarget = $page->panel()->url(true);
            }
        }

        return [
            'event'    => 'page.create',
            'redirect' => $redirectTarget
        ];
    }
}