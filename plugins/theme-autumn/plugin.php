<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

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
