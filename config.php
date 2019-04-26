<?php
use Kirby\Cms\Blueprint;
use Kirby\Cms\Section;

Kirby::plugin('steirico/kirby-plugin-custom-add-fields', [
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
                    foreach ($templates as $template) {
                        try {
                            $props = Blueprint::load('pages/' . $template['name']);
                            $addFields = A::get($props, 'addFields', null);
                            if($addFields){
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
            ]
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
