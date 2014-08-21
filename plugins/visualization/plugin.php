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
use Datawrapper\Visualization;

class DatawrapperPlugin_Visualization extends Plugin {
    public function init(Application $app) {
        $meta = $this->getMeta();
        if (!empty($meta)) Visualization::register($this, $meta);
    }

    public function getMeta() {
        return array();
    }
}
