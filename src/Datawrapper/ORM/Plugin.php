<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\ORM;

use Datawrapper\ORM\om\BasePlugin;

class Plugin extends BasePlugin {
    private $packageInfo;

    public function getName() {
        return $this->getId();
    }

    public function getClassName() {
        return 'DatawrapperPlugin_' . str_replace(' ', '', ucwords(str_replace('-', ' ', $this->getName())));
    }

    public function getPath() {
        return ROOT_PATH . 'plugins/' . $this->getName() . '/';
    }

    public function getInfo() {
        if (!isset($this->packageInfo)) {
            if (!file_exists($this->getPath() . 'package.json')) {
                return false;
            }
            $this->packageInfo = json_decode(
                file_get_contents($this->getPath() . 'package.json')
            , true);
            if (!isset($this->packageInfo['dependencies'])) $this->packageInfo['dependencies'] = array();
        }
        return $this->packageInfo;
    }

    public function getDependencies() {
        $info = $this->getInfo();
        if (isset($info['dependencies'])) {
            return $info['dependencies'];
        }
        return false;
    }

    /**
     * return plugin repository
     */
    public function getRepository() {
        $meta = $this->getInfo();
        if (isset($meta['repository'])) {
            return $meta['repository'];
        }
        return false;
    }
}
