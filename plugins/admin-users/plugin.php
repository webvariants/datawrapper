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

class DatawrapperPlugin_AdminUsers extends Plugin {
    public function init(Application $app) {
        $user   = Session::getUser();
        $plugin = $this;

        $app->get('/admin/users', 'DatawrapperPlugin_AdminUsers_Controller:indexAction');

        if ($user && $user->isAdmin()) {
            $app->get('/admin/users/:user_id', 'DatawrapperPlugin_AdminUsers_Controller:showAction');
        }

        // register plugin controller
        Hooks::register(Hooks::GET_ADMIN_PAGES, function() use ($plugin) {
            return array(
                'url'   => '/users',
                'title' => __('Users', $plugin->getName()),
                'order' => '2'
            );
        });

        $this->declareAssets(
            array(
                'vendor/serious-toolkit/serious-widget.js',
                'dw.admin.users.js',
                'users.css'
            ),
            '|/admin/users|'
        );
    }

    public function getRequiredLibraries() {
        return array('src/Controller.php');
    }
}
