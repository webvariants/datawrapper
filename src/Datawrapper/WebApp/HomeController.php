<?php

namespace Datawrapper\WebApp;

use Datawrapper\ORM;

class HomeController extends BaseController {
    public function indexAction() {
        $app = $this->disableCache()->getApp();

        // found link to a legacy chart
        $chartID = $app->request()->get('c');

        if ($chartID) {
            $app->redirect('/legacy/'.$chartID.'.html');
        }

        $chartIDs = array('RXoKw', 'a4Yyf', '78iap', 'weD23');
        $charts   = ORM\ChartQuery::create()->findPKs($chartIDs);

        $page = array(
            'title'         => '',
            'pageClass'     => 'home',
            'recent_charts' => $charts
        );

        add_header_vars($page, '');
        $app->render('home.twig', $page);
    }
}
