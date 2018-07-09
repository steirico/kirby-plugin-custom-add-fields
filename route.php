<?php
if(!function_exists('panel')) return;
panel()->routes(array(
  array(
    'pattern' => array('pages/(:any)/add', 'pages/(:any)/add/(:any)'),
    'action'  => function($id, $template='') {
      $panel  = panel();
      $parent = $panel->page($id);
      $url = $panel->urls()->current;

      if('' != $template){
        $url = preg_replace('/(.+)\/(.+)/m', '$1', $url);
        $panel->urls()->current = $url;
      }
      
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
          $page = $parent->children()->create($data['uid'], $data['template'], array(
            'title' => $data['title']
          ));
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