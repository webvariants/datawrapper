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
