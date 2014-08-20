<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp\Chart;

use Datawrapper\Hooks;
use Datawrapper\ORM;
use Datawrapper\Theme;
use Datawrapper\Visualization;
use Datawrapper\WebApp\BaseController;

class PublishController extends BaseController {
    /**
     * PUBLISH STEP - shows progress of publishing action and thumbnail generation
     * forwards to /chart/:id/finish
     */
    public function publishAction($chartID) {
        $app     = $this->disableCache()->getApp();
        $phantom = !!$this->getConfig('phantomjs');

        check_chart_writable($chartID, function($user, $chart) use ($app, $phantom) {
            $page = array(
                'title'             => $chart->getID().' :: '.__('Publish'),
                'chartData'         => $chart->loadData(),
                'chart'             => $chart,
                'visualizations'    => Visualization::all(),
                'vis'               => Visualization::get($chart->getType()),
                'chartUrl'          => $chart->getPublicUrl(),
                'chartUrlLocal'     => '/chart/'.$chart->getID().'/preview',
                'themes'            => Theme::all(),
                'exportStaticImage' => $phantom,
                'chartActions'      => Hooks::execute(Hooks::GET_CHART_ACTIONS, $chart),
                'estExportTime'     => ceil(ORM\JobQuery::create()->estimatedTime('export') / 60)
            );

            add_header_vars($page, 'chart', 'chart-editor/publish.css');
            add_editor_nav($page, 4);

            if ($user->isAbleToPublish() && ($chart->getLastEditStep() == 3 || $app->request()->get('republish') == 1)) {
                // actual publish process
                $chart->publish();
                $page['chartUrl'] = $chart->getPublicUrl();

                // generate thumbnails
                $page['publish']   = true;
                $page['republish'] = $app->request()->get('republish') == 1;
            }

            $app->render('chart/publish.twig', $page);
        });
    }
}
