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
