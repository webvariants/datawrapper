<?php

namespace Datawrapper\WebApp\Chart;

use Datawrapper\Hooks;
use Datawrapper\Theme;
use Datawrapper\Visualization;
use Datawrapper\WebApp\BaseController;

class VisualizeController extends BaseController {
    /**
     * VISUALIZE STEP
     */
    public function visualizeAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $debug = $this->getConfig('debug_export_test_cases');

        check_chart_writable($chartID, function($user, $chart) use ($app, $debug) {
            $page = array(
                'title'               => $chart->getID().' :: '.__('Visualize'),
                'chartData'           => $chart->loadData(),
                'chart'               => $chart,
                'visualizations_deps' => Visualization::all('dependencies'),
                'visualizations'      => Visualization::all(),
                'vis'                 => Visualization::get($chart->getType()),
                'themes'              => Theme::all(),
                'theme'               => Theme::get($chart->getTheme()),
                'debug'               => $debug ? '1' : '0'
            );

            add_header_vars($page, 'chart');
            add_editor_nav($page, 3);

            $app->render('chart/visualize.twig', $page);
        });
    }
}
