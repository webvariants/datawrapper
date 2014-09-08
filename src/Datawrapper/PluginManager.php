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

use Datawrapper\ORM\PluginQuery;
use \Criteria;

class PluginManager {
    protected static $init_queue = array();
    protected static $loaded = array();
    // instances of all (real) plugin classes
    protected static $instances = array();

    /*
     * loads plugin
     */
    public static function load() {
        if (defined('NO_SESSION')) {
            $plugins = PluginQuery::create()
                ->distinct()
                ->filterByEnabled(true)
                ->filterByIsPrivate(false)
                ->find();
        } else {
            $plugins = self::getUserPlugins(Session::getUser()->getId());
        }
        $not_loaded_yet = array();

        foreach ($plugins as $plugin) {
            if (!isset(self::$loaded[$plugin->getId()])) {
                $not_loaded_yet[] = $plugin;
            }
        }

        $could_not_install = array();

        while (count($not_loaded_yet) > 0) {
            $try = $not_loaded_yet;
            $not_loaded_yet = array();

            while (count($try) > 0) {
                $plugin = array_shift($try);
                $id     = $plugin->getId();
                $deps   = $plugin->getDependencies();

                unset($deps['core']);  // ignore core dependency
                $can_load = true;

                if (is_array($deps)) {
                    foreach ($deps as $dep => $version) {
                        if (!isset(self::$loaded[$dep])) {  // dependency not loaded
                            $can_load = false;
                            if (!file_exists(ROOT_PATH . 'plugins/' . $dep) || isset($could_not_install[$dep])) {
                                // dependency does not exists, not good
                                $could_not_install[$id] = true;
                            }
                            break;
                        }
                    }
                }

                // plugin already loaded by now?
                if (isset(self::$loaded[$id]) && self::$loaded[$id]) {
                    continue;
                }

                if ($can_load) {
                    // load plugin
                    self::$loaded[$id]    = true;
                    self::$instances[$id] = static::loadPlugin($plugin);
                }
                elseif (!isset($could_not_install[$id])) {
                    $not_loaded_yet[] = $plugin; // so try next time
                }
            }
        }

        // now initialize all plugins
        $app = Application::getInstance();

        while (count(self::$init_queue) > 0) {
            $pluginClass = array_shift(self::$init_queue);
            $pluginClass->init($app);
        }
    }

    public static function loadPlugin(ORM\Plugin $plugin) {
        $name   = $plugin->getName();
        $helper = new Plugin($name);
        $root   = $helper->getBaseDir();
        $autoload = true;

        // load old-school plugin
        if (file_exists($root.'plugin.php')) {
            // manuload the plugin class
            require $root.'plugin.php';

            // init plugin class
            $className   = 'DatawrapperPlugin_'.str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
            $pluginClass = new $className($name);
        }

        // load new-style plugin
        elseif (file_exists($root.'composer.json')) {
            $autoload = false;
            $composer = $helper->getComposerJSON();

            if (isset($composer['autoload'])) {
                $def    = $composer['autoload'];
                $loader = Application::getInstance()->dw_classloader;

                if (isset($def['psr-4'])) {
                    foreach ($def['psr-4'] as $prefix => $dir) {
                        $loader->setPsr4($prefix, $root.$dir);
                    }
                }

                if (isset($def['psr-0'])) {
                    foreach ($def['psr-0'] as $prefix => $dir) {
                        $loader->set($prefix, $root.$dir);
                    }
                }
            }

            if (!isset($composer['extra']['datawrapper']['plugin-class'])) {
                throw new \Exception('Plugin "'.$name.'" has no plugin-class defined in its composer.json!');
            }

            $className   = $composer['extra']['datawrapper']['plugin-class'];
            $pluginClass = new $className($name);
        }

        // load generic plugin
        else {
            $pluginClass = new Plugin($name);
        }

        // but before we load the libraries required by this lib
        if ($autoload) {
            foreach ($pluginClass->getRequiredLibraries() as $lib) {
                require_once ROOT_PATH.'plugins/'.$plugin->getName().'/'.$lib;
            }
        }

        self::$init_queue[] = $pluginClass;
        return $pluginClass;
    }

    public static function loaded($plugin_id) {
        return isset(self::$loaded[$plugin_id]) && self::$loaded[$plugin_id];
    }

    public static function getInstance($plugin_id) {
        if (isset(self::$instances[$plugin_id])) {
            return self::$instances[$plugin_id];
        }
        return null;
    }

    public static function getUserPlugins($user_id, $include_public=true) {
        $plugins = PluginQuery::create()
                ->distinct()
                ->filterByEnabled(true);

        if ($include_public) $plugins->filterByIsPrivate(false)->_or();

        if (!empty($user_id)) {
            $plugins
                ->useProductPluginQuery(null, Criteria::LEFT_JOIN)
                    ->useProductQuery(null, Criteria::LEFT_JOIN)
                        ->useOrganizationProductQuery(null, Criteria::LEFT_JOIN)
                            ->useOrganizationQuery(null, Criteria::LEFT_JOIN)
                                ->useUserOrganizationQuery(null, Criteria::LEFT_JOIN)
                                ->endUse()
                            ->endUse()
                        ->endUse()
                        ->useUserProductQuery(null, Criteria::LEFT_JOIN)
                        ->endUse()
                        ->where(
                            '((product.deleted=? AND user_product.user_id=? AND user_product.expires >= NOW())
                            OR (product.deleted=? AND user_organization.user_id=? AND organization_product.expires >= NOW()))',
                            array(false, $user_id, false, $user_id)
                        )
                    ->endUse()
                ->endUse();
        }
        return $plugins->find();
    }

    public static function listPlugins() {
        $plugins = array();
        foreach (self::$loaded as $id => $loaded) {
            if ($loaded) $plugins[] = array('id' => $id);
        }
        return $plugins;
    }
}

