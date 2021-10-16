<?php
use Kirby\Cms\Blueprint;
use Kirby\Cms\Section;
use Kirby\Cms\Find;
use Kirby\Panel\Field;


Kirby::plugin('steirico/kirby-plugin-custom-add-fields', [
    'options' => [
        'forcedTemplate.fieldName' => 'forcedTemplate',
        'forceTemplateSelectionField' => null
    ],
    'blueprints' => [
        'fields/default-add-fields' => __DIR__ . '/blueprints/fields/default-add-fields.yml',
    ],

    'areas' => [
        'site' => function ($kirby) {
            return [
                'dialogs' => [
                    // create a new page
                    'page.create' => [
                        'pattern' => 'pages/create',
                        'load' => function () {
                            $id = get('parent', 'site');
                            $object = Find::parent($id);
                            $section = get('section');
                            $templates = $object->blueprints($section);

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
                                    //TODO: Handle Skip dialog
                                    /*$now = time();
                                    $result = array(
                                        'skipDialog' => true,
                                        'page' => array(
                                            'template'  => $forcedTemplate,
                                            'slug' => $now,
                                            'content' => array (
                                                'title' => $now,
                                            )
                                        )
                                    );
                                    return $result;*/
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
                                    // Todo: Check how title field is handled in Kirby 3.6
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
                                            'field' =>  $id . "/addfields/" . $templateName . "/" . $name,
                                            'section' => $id . "/addsections/" . $templateName . "/" . $section,
                                            'model' => $id
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
                                        'parent'   => $id,
                                        'template' => $firstTemplate
                                    ]

                                ]
                            ];

                        },
                        'submit' => function () {
                            // TODO: Handle redirects
                            /*
                            $dialogProperties = A::get($addFields, '__dialog', null);
                            if($dialogProperties) {
                                $redirectToNewPage = A::get($addFields['__dialog'], 'redirect', false);
                                unset($addFields['__dialog']);
                            } else {
                                $redirectToNewPage = false;
                            }
                            */

                            $content = get();
                            unset($content['slug']);
                            unset($content['template']);

                            $slug = get('slug');
                            $slug = $slug == '' ? time() : $slug;


                            $page = Find::parent(get('parent', 'site'))->createChild([
                                'content'  => $content,
                                'slug'     => $slug,
                                'template' => get('template'),
                            ]);

                            return [
                                'event'    => 'page.create',
                                'redirect' => $page->panel()->url(true)
                            ];
                        }
                    ]
                ]
            ];
        }
    ],

    'api' => [
        'routes' => [
            [
                'pattern' => 'pages/(:any)/addsections/(:any)',
                'method'  => 'GET',
                'action'  => function (string $id, string $sectionName) {
                    // Todo: Test pages field
                    if ($section = $this->page($id)->blueprint()->section($sectionName)) {
                        return $section->toResponse();
                    }
                }
            ],
            [
                'pattern' => 'pages/(:any)/addfields/(:any)/(:any)/(:all?)',
                'method'  => 'ALL',
                'action'  => function (string $id, string $template, string $fieldName, string $path = null) {
                    // Todo: Test pages field
                    $object = $id == '' ? $this->site() : $this->page($id);
                    $dummyPage = Page::factory(array(
                        'url'    => null,
                        'num'    => null,
                        'parent' => $object,
                        'site'   => $object->site(),
                        'slug' => 'dummy',
                        'template' => $template,
                        'model' => $object,
                        'draft' => true,
                        'content' => []
                    ));

                    if ($dummyPage) {
                        $field = $this->fieldApi($dummyPage, $fieldName, $path);
                        return $field;
                    } else {
                        return null;
                    }
                }
            ],
        ]
    ],

    'hooks' => [
        'page.create:after' => function ($page) {
            $modelName = a::get(Page::$models, $page->intendedTemplate()->name());

            if($modelName && method_exists($modelName, 'hookPageCreate')){
                $modelName::hookPageCreate($page);
            }
        }
    ]
]);
