<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\ORM;
use Datawrapper\WebApp\AdminController;

class DatawrapperPlugin_AdminJobs_Controller extends AdminController {
    /**
     * controller for jobs admin
     */
    public function jobsAction() {
        $this->disableCache();

        $jobs = ORM\JobQuery::create()->filterByStatus('failed')->orderById('desc')->find();
        $page = array(
            'title'  => 'Background Jobs',
            'jobs'   => count($jobs) > 0 ? $jobs : false,
            'queued' => ORM\JobQuery::create()->filterByStatus('queued')->count(),
            'failed' => ORM\JobQuery::create()->filterByStatus('failed')->count(),
            'done'   => ORM\JobQuery::create()->filterByStatus('done')->count()
        );
        $page['est_time'] = ceil($page['queued'] * 2 / 60);

        $this->render('/jobs', 'plugins/admin-jobs/admin-jobs.twig', $page);
    }
}
