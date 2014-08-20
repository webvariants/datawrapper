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

class PreviewController extends BaseController {
    /**
     * Shows a preview of a chart for display in an iFrame
     */
    public function previewAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $i18n = $this->getI18N();

        check_chart_readable($chartID, function($user, $chart) use ($app, $i18n) {
            if ($chart->getLanguage() != '') {
                $i18n->loadMessages($chart->getLanguage());
            }

            $page = get_chart_content($chart, $user, $app->request()->get('minify'), $app->request()->get('debug'));

            $page['plain']      = $app->request()->get('plain') == 1;
            $page['fullscreen'] = $app->request()->get('fs') == 1;
            $page['innersvg']   = $app->request()->get('innersvg') == 1;

            $app->render('chart.twig', $page);
        });
    }

    public function nojsAction($chartID) {
        $app->redirect('/static/img/nojs.png');
    }
}
