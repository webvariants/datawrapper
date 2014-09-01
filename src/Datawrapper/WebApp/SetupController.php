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
use Datawrapper\Session;

class SetupController extends BaseController {
    public function indexAction() {
        $app = $this->disableCache()->getApp();

        if (Session::getUser()->isLoggedIn() || ORM\UserQuery::create()->filterByRole(array('admin', 'sysadmin'))->count() > 0) {
            $app->redirect('/');
        }

        $page = array(
            'title'     => 'Datawrapper',
            'pageClass' => 'setup',
            'noHeader'  => true,
            'noFooter'  => true,
            'noSignup'  => true,
            'auth_salt' => DW_AUTH_SALT
        );

        $this->setupHeaderVars($page, '');
        $app->render('setup.twig', $page);
    }

    public function setupAction() {
        $app  = $this->disableCache()->getApp();
        $data = json_decode($app->request()->getBody());

        // check that there is no admin user yet (only true right after setup)
        if (ORM\UserQuery::create()->count() == 0) {
            $user = new ORM\User();
            $user->setCreatedAt(time());
            $user->setEmail($data->email);
            $user->setRole('admin');
            $user->setPwd(secure_password($data->pwd));
            $user->setLanguage(Session::getLanguage());
            $user->save();

            Session::login($user);
            $app->redirect('/');
        }

        print json_encode(array('status' => 'fail'));
    }
}
