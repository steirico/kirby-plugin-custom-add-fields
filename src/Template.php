<?php

use Kirby\CMS\API;
use Kirby\CMS\Blueprint;
use Kirby\Cms\Section;
use Kirby\Toolkit\A;


class Template {

    protected $props;
    protected $name;
    protected $title;
    protected $dialog;


    public function __construct(array $props = []) {
        $this->props = $props;

        $this->name = A::get($this->props, 'name', '');
        $this->title = A::get($this->props, 'title', '');

        $this->dialog = new AddDialog('pages/' . $this->name);
    }

    public function name() {
        return  $this->name;
    }

    public function title() {
        return $this->title;
    }

    public function dialog() {
        return $this->dialog;
    }
}