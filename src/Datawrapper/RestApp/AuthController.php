<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\RestApp;

use Datawrapper\ORM\Action;
use Datawrapper\ORM\ActionQuery;
use Datawrapper\ORM\UserQuery;
use Datawrapper\Session;
use Datawrapper\Mailer;

class AuthController extends BaseController {
    /**
     * login user
     */
    public function loginAction() {
        $payload = json_decode($app->request()->getBody());
        //  v-- don't expire login anymore
        $user = UserQuery::create()->findOneByEmail($payload->email);
        if (!empty($user) && $user->getDeleted() == false) {
            if ($user->getPwd() === secure_password($payload->pwhash)) {
                Session::login($user, $payload->keeplogin == true);
                ok();
            } else {
                Action::logAction($user, 'wrong-password', json_encode(get_user_ips()));
                error('login-invalid', __('The password is incorrect.'));
            }
        } else {
            error('login-email-unknown', __('The email is not registered yet.'));
        }
    }

    /**
     * return the server salt for secure auth
     */
    public function saltAction() {
        ok(array('salt' => DW_AUTH_SALT));
    }

    /**
     * logs out the current user
     */
    public function logoutAction() {
        $user = Session::getUser();
        if ($user->isLoggedIn()) {
            Session::logout();
            ok();
        } else {
            error('not-loggin-in', 'you cannot logout if you\'re not logged in');
        }
    }
}
