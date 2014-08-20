<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Plugin;
use Datawrapper\Hooks;
use Datawrapper\ORM;

class DatawrapperPlugin_ExportStaticPng extends Plugin {

    public function init() {
        // hook into chart publication
        Hooks::register(Hooks::POST_CHART_PUBLISH, array($this, 'triggerExportJob'));

        // hook into job execution
        Hooks::register('export_static_chart', array($this, 'exportStaticPng'));
    }

    public function triggerExportJob($chart, $user) {
        // queue a job for thumbnail generation
        $params = array(
            'width'  => $chart->getMetadata('publish.embed-width'),
            'height' => $chart->getMetadata('publish.embed-height')
        );
        $job = ORM\JobQuery::create()->createJob('export_static_chart', $chart, $user, $params);
    }

    public function exportStaticPng($job) {
        $chart = $job->getChart();
        $params = $job->getParameter();
        $static_path = ROOT_PATH.'charts/static/'.$chart->getId().'/';

        // execute hook provided by phantomjs plugin
        // this calls phantomjs with the provided arguments
        $res = Hooks::execute(
            'phantomjs_exec',
            // path to the script
            ROOT_PATH.'plugins/'.$this->getName().'/gen_static_fallback.js',
            // url of the chart
            'http://'.$GLOBALS['dw_config']['domain'].'/chart/'. $chart->getId() .'/',
            // path to the image
            $static_path,
            // output width
            $params['width'],
            // output height
            $params['height']
        );

        if (empty($res[0])) {
            $job->setStatus('done');
            // upload to CDN if possible
            Hooks::execute(Hooks::PUBLISH_FILES, array(
                array(
                    $static_path.'static.html',
                    $chart->getId().'/'.$chart->getPublicVersion().'/static.html',
                    'text/html'
                ),
                array(
                    $static_path.'static.png',
                    $chart->getId().'/'.$chart->getPublicVersion().'/static.png',
                    'image/png'
                ),
                array(
                    $static_path.'nojs.png',
                    $chart->getId().'/'.$chart->getPublicVersion().'/nojs.png',
                    'image/png'
                )
            ));
        }
        else {
            // error message received, send log email
            dw_send_error_mail(
                sprintf('Generation of static fallback for chart [%s] failed', $chart->getId()),
                print_r($job->toArray()). "\n\nError:\n".$res[0]
            );

            $job->setStatus('failed');
            $job->setFailReason($res[0]);
        }
        $job->save();
    }
}
