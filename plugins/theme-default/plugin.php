<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Application;
use Datawrapper\Plugin;
use Datawrapper\Theme;

class DatawrapperPlugin_ThemeDefault extends Plugin {
    public function init(Application $app) {
        Theme::register($this, $this->getMeta());
    }

    private function getMeta() {
        return array(
            'id'      => 'default',
            'title'   => 'Datawrapper',
            'version' => '1.6.0'
        );
    }
}
