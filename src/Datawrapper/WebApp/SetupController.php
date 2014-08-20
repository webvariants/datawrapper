<?php

namespace Datawrapper\WebApp;

use Datawrapper\ORM\User;
use Datawrapper\ORM\UserQuery;
use Datawrapper\Session;

class SetupController extends BaseController {
    public function indexAction() {
        $app = $this->getApp();

        disable_cache($app);

        if (Session::getUser()->isLoggedIn() || UserQuery::create()->filterByRole(array('admin', 'sysadmin'))->count() > 0) {
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

        add_header_vars($page, '');
        $app->render('setup.twig', $page);
    }

    public function setupAction() {
        $app = $this->getApp();

        disable_cache($app);

        $data = json_decode($app->request()->getBody());

        // check that there is no admin user yet (only true right after setup)
        if (UserQuery::create()->count() == 0) {
            $user = new User();
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
