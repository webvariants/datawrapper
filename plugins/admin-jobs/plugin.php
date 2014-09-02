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
use Datawrapper\ORM;
use Datawrapper\Plugin;
use Datawrapper\Session;

class DatawrapperPlugin_AdminJobs extends Plugin {
    public function init(Application $app) {
        $user = Session::getUser();
        if (!$user || !$user->isAdmin()) return;

        $app->get('/admin/jobs', 'DatawrapperPlugin_AdminJobs_Controller:jobsAction');

        // register plugin controller
        $plugin = $this;

        Hooks::register(Hooks::GET_ADMIN_PAGES, function() use ($plugin) {
            // add badges to menu title
            $title = __('Jobs', $plugin->getName());

            $q = ORM\JobQuery::create()->filterByStatus('queued')->count();
            if ($q > 0) $title .= ' <span class="badge badge-info">'.$q.'</span>';

            $f = ORM\JobQuery::create()->filterByStatus('failed')->count();
            if ($f > 0) $title .= ' <span class="badge badge-important">'.$f.'</span>';

            return array(
                'url'   => '/jobs',
                'title' => $title,
                'order' => '10'
            );
        });
    }

    public function getRequiredLibraries() {
        return array('src/Controller.php');
    }
}
