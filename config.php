<?php
use Kirby\Cms\Page;
use Kirby\Toolkit\A;


Kirby::plugin('steirico/kirby-plugin-custom-add-fields', [
    'options' => [
        'forcedTemplate.fieldName' => 'forcedTemplate'
    ],

    'api' => [
        'routes' => [
            [
                'pattern' => [
                    'site/children/blueprints/add-fields',
                    'site/blueprints/add-fields',
                    'pages/(:any)/children/blueprints/add-fields',
                    'pages/(:any)/blueprints/add-fields',
                ],
                'method' => 'GET',
                'filter' => 'auth',
                'action'  => function (string $id = '') {
                    return (new CustomAddDialog($id, $this))->getAddFieldsData();
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
            $modelName = A::get(Page::$models, $page->intendedTemplate()->name());

            if(method_exists($modelName, 'hookPageCreate')){
                $modelName::hookPageCreate($page);
            }
        }
    ]
]);
