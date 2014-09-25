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
use Datawrapper\ORM\Chart;
use Datawrapper\Plugin;

class DatawrapperPlugin_AnalyticsPiwik extends Plugin {
	protected $app;
	
    public function init(Application $app) {
		$this->app = $app;

        Hooks::register(Hooks::CHART_AFTER_BODY, array($this, 'getTrackingCode'));
        Hooks::register(Hooks::CORE_AFTER_BODY,  array($this, 'getTrackingCode'));
    }

    public function getTrackingCode(Chart $chart = null) {
        $config = $this->getConfig();
        if (empty($config)) return false;

        $this->app->render('plugins/analytics-piwik/piwik-code.twig', array(
            'url'    => $config['url'],
            'idSite' => $config['idSite'],
            'chart'  => $chart,
            'user'   => $chart ? $chart->getUser() : null
		));
    }
}
