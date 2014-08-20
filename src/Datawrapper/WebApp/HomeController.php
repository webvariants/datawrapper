<?php

namespace Datawrapper\WebApp;

use Datawrapper\ORM\ChartQuery;

class HomeController extends BaseController {
    public function indexAction() {
        $app = $this->getApp();

        disable_cache($app);

        if ($app->request()->get('c')) {
            // found link to a legacy chart
            $app->redirect('/legacy/'.$app->request()->get('c').'.html');
        }

        $chart_ids = array('RXoKw', 'a4Yyf', '78iap', 'weD23');
        $charts    = ChartQuery::create()->findPKs($chart_ids);

        $page = array(
            'title'         => '',
            'pageClass'     => 'home',
            'recent_charts' => $charts
        );

        add_header_vars($page, '');
        $app->render('home.twig', $page);
    }
}
