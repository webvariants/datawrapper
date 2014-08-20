<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

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
