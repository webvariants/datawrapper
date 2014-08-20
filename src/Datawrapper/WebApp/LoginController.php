<?php

namespace Datawrapper\WebApp;

use Datawrapper\Session;

class LoginController extends BaseController {
    public function indexAction() {
        $app = $this->disableCache()->getApp();

        if (Session::getUser()->isLoggedIn()) {
            $app->redirect('/');
        }

        $page = array(
            'title'     => 'Datawrapper',
            'pageClass' => 'login',
            'noHeader'  => true,
            'noFooter'  => true,
            'noSignup'  => true
        );

        add_header_vars($page, '');
        $app->render('login-page.twig', $page);
    }
}
