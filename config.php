<?php
use Steineri\CustomAddFields\Plugin;

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
                            return Plugin::loadPageCreate();
                        },
                        'submit' => function () {
                            return Plugin::submitPageCreate(get());
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
