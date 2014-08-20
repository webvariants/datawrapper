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

class EmbedController extends BaseController {
    /**
     * Main controller for chart rendering
     */
    public function getAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $i18n = $this->getI18N();

        check_chart_public($chartID, function($user, $chart) use ($app, $i18n) {
            if ($chart->getLanguage() != '') {
                $i18n->loadMessages($chart->getLanguage());
            }

            $page = get_chart_content($chart, $user, $app->request()->get('minify') == 1);

            $page['thumb']      = $app->request()->params('t') == 1;
            $page['innersvg']   = $app->request()->get('innersvg') == 1;
            $page['plain']      = $app->request()->get('plain') == 1;
            $page['fullscreen'] = $app->request()->get('fs') == 1;

            $app->render('chart.twig', $page);
        });
    }

    public function redirectAction($chartID) {
        $app->redirect('/chart/'.$chartID.'/');
    }
}
