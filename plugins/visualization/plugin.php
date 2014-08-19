<?php

use Datawrapper\Plugin;
use Datawrapper\Visualization;

class DatawrapperPlugin_Visualization extends Plugin {
    public function init() {
        $meta = $this->getMeta();
        if (!empty($meta)) Visualization::register($this, $meta);
    }

    public function getMeta() {
        return array();
    }
}
