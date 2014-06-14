<?php

use Datawrapper\Visualization;
use Datawrapper\Theme;

/*
 * VISUALIZE STEP
 */
$app->get('/chart/:id/visualize', function ($id) use ($app) {
    disable_cache($app);

    check_chart_writable($id, function($user, $chart) use ($app) {
        $page = array(
            'title' => $chart->getID() . ' :: '.__('Visualize'),
            'chartData' => $chart->loadData(),
            'chart' => $chart,
            'visualizations_deps' => Visualization::all('dependencies'),
            'visualizations' => Visualization::all(),
            'vis' => Visualization::get($chart->getType()),
            'themes' => Theme::all(),
            'theme' => Theme::get($chart->getTheme()),
            'debug' => !empty($GLOBALS['dw_config']['debug_export_test_cases']) ? '1' : '0'
        );
        add_header_vars($page, 'chart');
        add_editor_nav($page, 3);

        $app->render('chart/visualize.twig', $page);
    });
});

