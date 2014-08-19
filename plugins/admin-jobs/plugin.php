<?php

use Datawrapper\Plugin;
use Datawrapper\Hooks;
use Datawrapper\ORM;

class DatawrapperPlugin_AdminJobs extends Plugin {
    public function init() {
        $plugin = $this;
        // register plugin controller
        Hooks::register(
            Hooks::GET_ADMIN_PAGES,
            function() use ($plugin) {
                // add badges to menu title
                $title = __('Jobs', $plugin->getName());
                $q = ORM\JobQuery::create()->filterByStatus('queued')->count();
                if ($q > 0) $title .= ' <span class="badge badge-info">'.$q.'</span>';
                $f = ORM\JobQuery::create()->filterByStatus('failed')->count();
                if ($f > 0) $title .= ' <span class="badge badge-important">'.$f.'</span>';

                return array(
                    'url'        => '/jobs',
                    'title'      => $title,
                    'controller' => array($plugin, 'jobsAdmin'),
                    'order'      => '10'
                );
            }
        );
    }

    /*
     * controller for jobs admin
     */
    public function jobsAdmin($app, $page) {
        $jobs = ORM\JobQuery::create()->filterByStatus('failed')->orderById('desc')->find();
        $page = array_merge($page, array(
            'title'  => 'Background Jobs',
            'jobs'   => count($jobs) > 0 ? $jobs : false,
            'queued' => ORM\JobQuery::create()->filterByStatus('queued')->count(),
            'failed' => ORM\JobQuery::create()->filterByStatus('failed')->count(),
            'done'   => ORM\JobQuery::create()->filterByStatus('done')->count()
        ));
        $page['est_time'] = ceil($page['queued'] * 2 / 60);

        $app->render('plugins/admin-jobs/admin-jobs.twig', $page);
    }
}
