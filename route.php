<?php
if(!function_exists('panel')) return;
panel()->routes(array(
  array(
    'pattern' => array('(?:(?!.+\/field\/.+))pages/(.+)/add', '(?:(?!.+\/field\/.+))pages/(.+)/add/(:any)'),
    'action'  => function($id, $template='') {
      $panel  = panel();
      $parent = $panel->page($id);
      $url = $panel->urls()->current;
      
      $controller = new Kirby\Panel\Controllers\Base();
      if($parent->ui()->create() === false) {
        throw new PermissionsException();
      }
      $form = $panel->form(__DIR__ . DS . 'default-add-Form.php', array($parent, $template), function($form) use($parent, $controller) {
        try {
          $form->validate();
          if(!$form->isValid()) {
            throw new Exception(l('pages.add.error.template'));
          }
          
          $data = $form->serialize();

          if(array_key_exists('uid', $data)){
            $uid = $data['uid'];
            unset($data['uid']);
          } else if(array_key_exists('title', $data)){
            $uid = str::slug($data['title']);
          } else {
            $uid = uniqid();
          }

          if(!array_key_exists('title', $data)){
            $data['title'] = $uid;
          }

          $template = $data['template'];
          unset($data['template']);

          $page = $parent->children()->create($uid, $template, $data);

          $controller->notify(':)');
          $controller->redirect($page, 'edit');
        } catch(Exception $e) {
          $form->alert($e->getMessage());
        }
      });
      $content = tpl::load(__DIR__ . DS . 'modal-template.php', array('form'=>$form, 'url'=>$url));
      return $controller->layout('app', compact('content'));
    },
    'filter' => 'auth',
    'method' => 'POST|GET',
  )
));