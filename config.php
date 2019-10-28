<?php
use Kirby\Cms\Blueprint;
use Kirby\Cms\Section;

Kirby::plugin('steirico/kirby-plugin-custom-add-fields', [
    'options' => [
        'forcedTemplate.fieldName' => 'forcedTemplate'
    ],

    'api' => [
        'routes' => [
            [
                'pattern' => [
                    'site/children/blueprints/add-fields',
                    'pages/(:any)/children/blueprints/add-fields',
                ],
                'method' => 'GET',
                'filter' => 'auth',
                'action'  => function (string $id = '') {
                    $result = [];
                    $object = $id == '' ? $this->site() : $this->page($id);
                    $templates = $object->blueprints($this->requestQuery('section'));

                    $forcedTemplateFieldName = option('steirico.kirby-plugin-custom-add-fields.forcedTemplate.fieldName');
                    $forcedTemplateFieldName = $forcedTemplateFieldName ? $forcedTemplateFieldName : '';
                    $hasForcedTemplate = $object->content()->has($forcedTemplateFieldName);

                    if($hasForcedTemplate){
                        $forcedTemplate = $object->{$forcedTemplateFieldName}()->value();
                    }

                    foreach ($templates as $template) {
                        if($hasForcedTemplate && $template['name'] != $forcedTemplate){
                            continue;
                        }
                        try {
                            $props = Blueprint::load('pages/' . $template['name']);
                            $addFields = A::get($props, 'addFields', null);
                            if($addFields){
                                $fieldOrder = array_change_key_case($addFields, CASE_LOWER);

                                $title = A::get($addFields, 'title', null);
                                $attr = [
                                    'model' => $object,
                                    'fields' => $addFields
                                ];
                                $addSection = new Section('fields', $attr);
                                $addFields = $addSection->fields();

                                if($title){
                                    $addFields['title'] = $title;
                                }

                                $addFields = array_replace($fieldOrder, $addFields);

                            }
                            array_push($result, [
                                'name'  => $template['name'],
                                'title' => $template['title'],
                                'addFields' => $addFields,
                                'parentPage' => $id
                            ]);
                        } catch (Throwable $e) {
                        }
                    }
                    return $result;
                }
            ],
            [
                'pattern' => 'pages/(:any)/addsections/(:any)',
                'method'  => 'GET',
                'action'  => function (string $id, string $sectionName) {
                    if ($section = $this->page($id)->blueprint()->section($sectionName)) {
                        return $section->toResponse();
                    }
                }
            ],
            [
                'pattern' => 'pages/(:any)/addfields/(:any)/(:any)/(:all?)',
                'method'  => 'ALL',
                'action'  => function (string $id, string $template, string $fieldName, string $path = null) {
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

            if(method_exists($modelName, 'hookPageCreate')){
                $modelName::hookPageCreate($page);
            }
        }
    ]
]);
