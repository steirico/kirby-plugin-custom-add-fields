<?php

use Kirby\CMS\API;
use Kirby\CMS\Blueprint;
use Kirby\Cms\Section;
use Kirby\Toolkit\A;


class AddDialog {

    protected $template;
    protected $options;
    protected $fields;

    public function __construct(string $template = '') {
        $this->template = $template;
        if ($template != 'site' && strpos($template, 'pages/') === false) {
            $template = 'pages/' . $template;
        }

        $props = Blueprint::load($template);
        $this->template = $template;
        $fieldsProps = A::get($props, 'addFields', null);
        $this->options = A::get($fieldsProps, '__dialog', null);
        $this->fields = $fieldsProps;
        unset($this->fields['__dialog']);
    }

    public function fields() {
        return $this->fields;
    }

    public function options() {
        return $this->options;
    }

    public function skipDialogData(AddDialog $parentDialog) {
        $now = time();
        $result = array(
            'options' => $parentDialog->options(),
            'page' => array(
                'template'  => $this->template,
                'slug' => $now,
                'content' => array(
                    'title' => $now,
                )
            )
        );
        return $result;
    }

    public function skip() {
        return A::get($this->options, 'skip', false);
    }

    public function forcedTemplate() {
        return A::get($this->options, 'forcedTemplate', '');
    }
}