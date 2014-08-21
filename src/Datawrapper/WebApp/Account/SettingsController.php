<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp\Account;

use Datawrapper\ORM;
use Datawrapper\Session;
use Datawrapper\WebApp\AccountController;

class SettingsController extends AccountController {
    /**
     * this page shows up if an user has been invited to
     * datawrapper and therefor only needs to pick a password
     * to complete the registration process.
     */
    public function settingsAction() {
        $app   = $this->disableCache()->getApp();
        $pages = $this->getAccountPages();
        $user  = Session::getUser();
        $page  = array();

        if ($user->getRole() == 'guest') {
            error_page('user',
                __('Whoops! You need to be logged in.'),
                __('Guess what, in order to edit your user profile, you need to either login or create yourself an account.')
            );
            return;
        }

        if ($user->isAdmin()) {
            // admins can edit settings for other users
            $req = $app->request();

            if ($req->get('uid') != null) {
                $u = ORM\UserQuery::create()->findPk($req->get('uid'));

                if ($u) {
                    $user = $page['user'] = $u;
                    $page['api_user'] = $user->getId();
                }
            }
        }

        if ($app->request()->get('token')) {
            // look for action with this token
            $t = ORM\ActionQuery::create()
                ->filterByUser($user)
                ->filterByKey('email-change-request')
                ->orderByActionTime('desc')
                ->findOne();

            if (!empty($t)) {
                // check if token is valid
                $params = json_decode($t->getDetails(), true);

                if (!empty($params['token']) && $params['token'] == $app->request()->get('token')) {
                    // token matches
                    $user->setEmail($params['new-email']);
                    $user->save();

                    $page['new_email_confirmed'] = true;

                    // clear token to prevent future changes
                    $params['token'] = '';

                    $t->setDetails(json_encode($params));
                    $t->save();
                }
            }
        }

        if ($user->getRole() == 'pending') {
            $t = ORM\ActionQuery::create()
                ->filterByUser($user)
                ->filterByKey('resend-activation')
                ->orderByActionTime('desc')
                ->findOne();

            if (empty($t)) {
                $t = $user->getCreatedAt('U');
            }
            else {
                $t = $t->getActionTime('U');
            }

            $page['activation_email_date'] = strftime('%x', $t);
        }

        $this->render('settings', 'settings.twig', $page);
    }
}
