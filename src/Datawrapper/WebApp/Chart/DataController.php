<?php

namespace Datawrapper\WebApp\Chart;

use Datawrapper\ORM;
use Datawrapper\WebApp\BaseController;

class DataController extends BaseController {
    public function dataAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $res   = $app->response();
        $chart = ORM\ChartQuery::create()->findPK($chartID);

        $res['Cache-Control']       = 'max-age=0';
        $res['Content-Type']        = 'text/csv';
        $res['Content-Disposition'] = 'attachment; filename="datawrapper-'.$chartID.'.csv"';

        if ($chart) {
            print $chart->loadData();
        }
        else {
            error_chart_not_found($chartID);
        }
    }
}
