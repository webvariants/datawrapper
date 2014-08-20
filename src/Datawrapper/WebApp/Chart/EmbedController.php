<?php

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
