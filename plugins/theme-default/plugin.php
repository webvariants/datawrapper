<?php

use Datawrapper\Plugin;
use Datawrapper\Theme;

class DatawrapperPlugin_ThemeDefault extends Plugin {
    public function init() {
        Theme::register($this, $this->getMeta());
    }

    private function getMeta() {
        return array(
            'id'      => 'default',
            'title'   => 'Datawrapper',
            'version' => '1.5.2'
        );
    }
}
