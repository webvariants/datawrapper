<?php

use Datawrapper\Plugin;
use Datawrapper\Hooks;

class DatawrapperPlugin_Phantomjs extends Plugin {
    public function init() {
        Hooks::register('phantomjs_exec', array($this, 'executeScript'));
    }

    public function executeScript() {
        $args = func_get_args();
        return $this->exec(implode(' ', $args));
    }

    private function exec($cmd, &$error=null) {
        $cfg = $this->getConfig();
        ob_start();  // grab output
        passthru($cfg['path'] . ' ' . $cmd, $error);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}
