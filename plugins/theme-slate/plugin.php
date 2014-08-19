<?php

use Datawrapper\Plugin;
use Datawrapper\Theme;

class DatawrapperPlugin_ThemeSlate extends Plugin {
    public function init() {
        Theme::register($this, $this->getMeta());
    }

    private function getMeta() {
        return array(
            'id'         => 'slate',
            'title'      => 'Noir',
            'link'       => 'http://bootswatch.com/slate/',
            'restricted' => null,
            'version'    => '1.5.0',
        );
    }
}
