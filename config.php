<?php
use Kirby\Cms\Blueprint;

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
                            array_push($result, [
                                'name'  => $template['name'],
                                'title' => $template['title'],
                                'addFields' => $props['addFields'],
                                'parentPage' => $id
                            ]);
                        } catch (Throwable $e) {
                            $result = $templates;
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
