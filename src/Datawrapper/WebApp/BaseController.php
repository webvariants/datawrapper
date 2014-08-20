<?php

namespace Datawrapper\WebApp;

use Slim\Slim;

class BaseController {
    protected function getApp() {
        return Slim::getInstance();
    }

    protected function getI18N() {
        return $GLOBALS['__l10n'];
    }

    protected function disableCache() {
        $app = $this->getApp();
        disable_cache($app);

        return $this;
    }

    protected function getConfig($key = null) {
        $config = $GLOBALS['dw_config'];

        if ($key !== null) {
            return array_key_exists($key, $config) ? $config[$key] : null;
        }

        return $config;
    }
}
