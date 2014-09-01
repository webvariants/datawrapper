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

class ActivateController extends AccountController {
    public function activateAction($token) {
        $app    = $this->disableCache()->getApp();
        $params = '';
        $page   = array();

        $this->setupHeaderVars($page, 'about');

        if (!empty($token)) {
            $users = ORM\UserQuery::create()->filterByActivateToken($token)->find();

            if (count($users) != 1) {
                $params = '?t=e&m='.urlencode(__('This activation token is invalid. Your email address is probably already activated.'));
            }
            else {
                $user = $users[0];
                $user->setRole('editor');
                $user->setActivateToken('');
                $user->save();

                $params = '?t=s&m='.urlencode(sprintf(__('Your email address %s has been successfully activated!'), $user->getEmail()));
            }
        }

        $app->redirect('/'.$params);
    }

    /**
     * check invitation token and show invited page
     */
    public function inviteAction($token) {
        $app = $this->disableCache()->getApp();

        $this->checkInviteTokenAndExec($token, function($user) use ($app) {
            $page = array(
                'email'     => $user->getEmail(),
                'auth_salt' => DW_AUTH_SALT
            );

            $app->dw_header->addVars($page, 'about', 'account/invite.min.css');
            $app->render('account/invite.twig', $page);
        });
    }

    /**
     * store new password, clear invitation token and login
     */
    public function doInviteAction($token) {
        $app = $this->disableCache()->getApp();

        $this->checkInviteTokenAndExec($token, function($user) use ($app) {
            $data = json_decode($app->request()->getBody());

            $user->setPwd($data->pwd);
            $user->setActivateToken('');
            $user->save();

            Session::login($user);

            print json_encode(array('result' => 'ok'));
        });
    }

    protected function checkInviteTokenAndExec($token, $callback) {
        if (!empty($token)) {
            $user = ORM\UserQuery::create()->findOneByActivateToken($token);

            if ($user && $user->getRole() != 'pending') {
                $callback($user);
            }
            else {
                $page['alert'] = array(
                    'type'    => 'error',
                    'message' => __('The invitation token is invalid.')
                );

                $this->getApp()->redirect('/');
            }
        }
    }
}
