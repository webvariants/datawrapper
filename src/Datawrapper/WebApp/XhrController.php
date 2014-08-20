<?php

namespace Datawrapper\WebApp;

use Datawrapper\Session;
use Datawrapper\Theme;
use Datawrapper\Visualization;

/**
 * this controller returns small pieces of the UI
 */
class XhrController extends BaseController {
    /**
     * reloads the header menu after login/logout
     */
    public function headerAction($active) {
        $app = $this->disableCache()->getApp();

        $res = $app->response();
        $res['Cache-Control'] = 'max-age=0';

        $page = array();

        add_header_vars($page, $active);
        $app->render('header.twig', $page);
    }

    /**
     * reloads the header menu after login/logout
     */
    public function homeLoginAction() {
        $app = $this->disableCache()->getApp();

        $res = $app->response();
        $res['Cache-Control'] = 'max-age=0';

        $page = array();

        add_header_vars($page);
        $app->render('home-login.twig', $page);
    }

    /**
     * reloads visualization specific options after the user
     * changed the visualization type
     */
    public function visOptionsAction($id) {
        $app = $this->disableCache()->getApp();

        check_chart_writable($id, function($user, $chart) use ($app) {
            $page = array(
                'vis'      => Visualization::get($chart->getType()),
                'theme'    => Theme::get($chart->getTheme()),
                'language' => substr(Session::getLanguage(), 0, 2)
            );

            $app->render('chart/visualize/options.twig', $page);
        });
    }
}
