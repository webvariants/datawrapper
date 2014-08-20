<?php

namespace Datawrapper\WebApp\Chart;

use Datawrapper\WebApp\BaseController;

class EditController extends BaseController {
    public function editAction($chartID) {
        $app = $this->disableCache()->getApp();

        check_chart_exists($chartID, function($chart) use ($app) {
            $step = 'upload';

            switch ($chart->getLastEditStep()) {
                case 0:
                case 1:  $step = 'upload';   break;
                case 2:  $step = 'describe'; break;
                default: $step = 'visualize#tell-the-story';
            }

            $app->redirect('/chart/'.$chart->getId().'/'.$step);
        });
    }
}
