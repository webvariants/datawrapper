<?php

namespace Datawrapper\WebApp\Chart;

use Datawrapper\Hooks;
use Datawrapper\WebApp\BaseController;

class UploadController extends BaseController {
    /**
     * UPLOAD STEP
     */
    public function uploadAction($chartID) {
        $app = $this->disableCache()->getApp();

        check_chart_writable($chartID, function($user, $chart) use ($app) {
            $page = array(
                'title'     => $chart->getID().' :: '.__('Upload Data'),
                'chartData' => $chart->loadData(),
                'chart'     => $chart,
                'datasets'  => Hooks::execute(Hooks::GET_DEMO_DATASETS)
            );

            add_header_vars($page, 'chart');
            add_editor_nav($page, 1);

            $res = $app->response();
            $res['Cache-Control'] = 'max-age=0';

            $app->render('chart/upload.twig', $page);
        });
    }
}
