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
