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
use Datawrapper\Hooks;
use Datawrapper\Plugin;

class DatawrapperPlugin_Phantomjs extends Plugin {
    public function init(Application $app) {
        Hooks::register('phantomjs_exec', array($this, 'executeScript'));
    }

    public function executeScript() {
        $args = func_get_args();
        return $this->exec(implode(' ', $args));
    }

    private function exec($cmd, &$error = null) {
        $cfg = $this->getConfig();
        ob_start();  // grab output
        passthru($cfg['path'].' '.$cmd, $error);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}
