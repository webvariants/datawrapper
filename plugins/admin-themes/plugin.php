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

class DatawrapperPlugin_AdminThemes extends Plugin {
    public function init(Application $app) {
        $app->get('/admin/themes', 'DatawrapperPlugin_AdminThemes_Controller:themesAction');

        // register plugin controller
        $plugin = $this;

        Hooks::register(Hooks::GET_ADMIN_PAGES, function() use ($plugin) {
            return array(
                'url'   => '/themes',
                'title' => __('Themes', $plugin->getName()),
                'order' => '3'
            );
        });
    }

    public function getRequiredLibraries() {
        return array('src/Controller.php');
    }
}
