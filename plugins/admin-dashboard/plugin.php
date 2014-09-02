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
use Datawrapper\Session;

class DatawrapperPlugin_AdminDashboard extends Plugin {
    public function init(Application $app) {
        $user = Session::getUser();
        if (!$user || !$user->isAdmin()) return;

        $app->get('/admin/?', 'DatawrapperPlugin_AdminDashboard_Controller:dashboardAction');

        // register plugin controller
        $pluginName = $this->getName();

        Hooks::register(Hooks::GET_ADMIN_PAGES, function() use ($pluginName) {
            return array(
                'url'   => '/',
                'title' => __('Dashboard', $pluginName),
                'order' => '1'
            );
        });
    }

    public function getRequiredLibraries() {
        return array('src/Controller.php');
    }
}
