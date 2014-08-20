<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper;

class Theme {
    private static $instance;

    private $themes = array();

    public static function getInstance() {
        if (!isset(self::$instance)) self::$instance = new static();
        return self::$instance;
    }

    /**
     * registers a new visualization, should be called by plugins
     */
    public static function register($plugin, $meta) {
        return self::getInstance()->_register($plugin, $meta);
    }

    /**
     * returns a list of all visualization meta arrays
     */
    public static function all($ignoreRestrictions = false) {
        return self::getInstance()->_all($ignoreRestrictions);
    }

    /**
     * returns one specific visualization meta array
     */
    public static function get($id) { return self::getInstance()->_get($id); }

    public function _register($plugin, $meta) {
        // we save the path to the static files of the visualization
        $meta['__static_path'] =  '/static/plugins/' . $plugin->getName() . '/';
        $meta['__template_path'] =  '/plugins/' . $plugin->getName() . '/';
        $meta['version'] = $plugin->getVersion();
        $this->themes[$meta['id']] = $meta;
    }

    private function _all($ignoreRestrictions) {
        $res = array_values($this->themes);
        $user = Session::getInstance()->getUser();
        $email = $user->getEmail();
        $domain = substr($email, strpos($email, '@'));

        $res = array();

        foreach ($this->themes as $meta) {
            $res[] = $meta;
        }

        return $res;
    }

    private function _get($id) {
        if (!isset($this->themes[$id])) return false;
        $meta = $this->themes[$id];
        $tpl_file = $meta['__template_path'] . $meta['id'] . '.twig';
        $parent = false;

        if (isset($meta['extends'])) {
            $parent = $this->themes[$meta['extends']];
            $parent_tpl_file = $parent['__template_path'] . $parent['id'] . '.twig';
        }
        else {
            $meta['extends'] = false;
        }

        if (file_exists(ROOT_PATH . 'templates/' . $tpl_file)) {
            $meta['template'] = $tpl_file;
        }
        else if ($parent && file_exists(ROOT_PATH . 'templates/' . $parent_tpl_file)) {
            $meta['template'] = $parent_tpl_file;
        }

        $meta['hasStyles'] = file_exists(ROOT_PATH . 'www/' . $meta['__static_path'] . $id . '.css');
        return $meta;
    }
}
