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
