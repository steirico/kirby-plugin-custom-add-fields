<?php
if(!function_exists('kirby')) return;
kirby()->hook('panel.page.create', function($page) {
    $modelName = a::get(Page::$models, $page->intendedTemplate());
    $modelName = preg_replace('/\\\\(.+)/m', '$1', $modelName);

    if(method_exists($modelName, 'hookPageCreate')){
        $modelName::hookPageCreate($page);
    }    
});