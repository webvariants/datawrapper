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
use Datawrapper\Visualization;

class DatawrapperPlugin_Gallery extends Plugin {
    public function init(Application $app) {
        // setup routing
        $app->get('/gallery/?',            'DatawrapperPlugin_Gallery_Controller:indexAction');
        $app->get('/gallery/by/:key/:val', 'DatawrapperPlugin_Gallery_Controller:filterAction');

        // show link 'show in gallery'
        Hooks::register(Hooks::PUBLISH_AFTER_CHART_ACTIONS, function() use ($app) {
            $app->render('plugins/gallery/show-in-gallery.twig');
        });

        // show link to gallery in mycharts page
        Hooks::register(Hooks::MYCHARTS_AFTER_SIDEBAR, function() use ($app) {
            $app->render('plugins/gallery/take-a-look.twig');
        });

        if (!Session::getUser()->isLoggedIn()) {
            $this->addHeaderNav('mycharts', array(
                'url'   => '/gallery/',
                'id'    => 'gallery',
                'title' => __('Gallery'),
                'icon'  => 'signal'
            ));
        }
    }

    public function getRequiredLibraries() {
        return array('src/Controller.php');
    }
}
