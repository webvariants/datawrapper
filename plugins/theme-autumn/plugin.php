<?php

use Datawrapper\Plugin;
use Datawrapper\Theme;

class DatawrapperPlugin_ThemeAutumn extends Plugin {
    public function init() {
        Theme::register($this, $this->getMeta());
    }

    private function getMeta() {
        return array(
            'id'            => 'autumn',
            'title'         => 'Playfair',
            'link'          => 'http://www.datawrapper.de',
            'extends'       => 'default',
            'restricted'    => null,
            'version'       => '1.5.0',
            'option-filter' => array(
                'line-chart' => array(
                    'show-grid' => true
                )
            )
        );
    }
}
