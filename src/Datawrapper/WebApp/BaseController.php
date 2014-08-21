<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp;

use Datawrapper\Application;

class BaseController {
    protected function getApp() {
        return Application::getInstance();
    }

    protected function getI18N() {
        return $this->getApp()->getI18N();
    }

    protected function getConfig($key = null) {
        return $this->getApp()->getConfig($key);
    }

    protected function disableCache() {
        $app = $this->getApp();
        disable_cache($app);

        return $this;
    }
}
