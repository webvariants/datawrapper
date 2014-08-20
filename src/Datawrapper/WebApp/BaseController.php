<?php

namespace Datawrapper\WebApp;

use Slim\Slim;

class BaseController {
    protected function getApp() {
        return Slim::getInstance();
    }
}
