<?php
if(!function_exists('kirby')) return;
kirby()->hook('panel.page.create', function($page) {

    $modelName = array_values(Page::$models)[0];
    $modelName = preg_replace('/\\\\(.+)/m', '$1', $modelName);

    if(method_exists($modelName, 'hookPageCreate')){
        $pageInstance = new $modelName($page->parent(), $page->dirname());
        $pageInstance->hookPageCreate();
    }    
});