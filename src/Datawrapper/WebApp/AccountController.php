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

use Datawrapper\ErrorPage;
use Datawrapper\Hooks;
use Datawrapper\ORM;
use Datawrapper\Session;

class AccountController extends BaseController {
    public static function registerDefaultPages() {
        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Settings'),
                'order' => 5,
                'icon'  => 'fa-wrench',
                'url'   => 'settings'
            );
        });

        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Delete account'),
                'order' => 9999,
                'icon'  => 'fa-frown-o',
                'url'   => 'delete'
            );
        });

        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Change password'),
                'order' => 10,
                'icon'  => 'fa-lock',
                'url'   => 'password'
            );
        });
    }

    public function redirectAction() {
        $app   = $this->getApp();
        $pages = $this->getAccountPages();
        $first = reset($pages);

        $app->redirect('/account/'.$first['url']);
    }

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
            ErrorPage::show('user',
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

    /**
     * this page shows up if an user has been invited to
     * datawrapper and therefor only needs to pick a password
     * to complete the registration process.
     */
    public function setPasswordAction($token) {
        $app   = $this->disableCache()->getApp();
        $pages = $this->getAccountPages();

        if (!empty($token)) {
            $users = ORM\UserQuery::create()->filterByActivateToken($token)->find();

            if (count($users) != 1) {
                $app->redirect('/?t=e&m='.__('This activation token is invalid. Your email address is probably already activated.'));
            }

            $page = array();
            $this->setupHeaderVars($page, 'about');

            $page['salt'] = DW_AUTH_SALT;
            $app->render('account/set-password.twig', $page);
        }
        else {
            $app->notFound();
        }
    }

    public function resetPasswordAction($token) {
        $app   = $this->disableCache()->getApp();
        $pages = $this->getAccountPages();
        $page  = array();

        $this->setupHeaderVars($page, 'account');

        if (!empty($token)) {
            $users = ORM\UserQuery::create()->filterByResetPasswordToken($token)->find();

            if (count($users) != 1) {
                $page['alert'] = array(
                    'type'    => 'error',
                    'message' => 'This activation token is invalid.'
                );

                ErrorPage::show('user',
                    __('Something went horribly wrong'),
                    __('The password reset link you entered is invalid.'),
                    array(
                        __('Re-check the link you received in our email. Make sure you copied the full link and try again.'),
                        __('Contact someone of our friendly <a href="mailto:hello@datawrapper.de">administrators</a> and ask for help with the password reset process.')
                    )
                );
            }
            else {
                $user = $users[0];
                // $user->setResetPasswordToken('');
                // $user->save();
                $page['token'] = $token;

                $app->render('account/reset-password.twig', $page);
            }
        }
    }

    public function deleteFormAction() {
        $this->disableCache();
        $this->render('delete', 'account/delete.twig');
    }

    public function passwordFormAction() {
        $this->disableCache();
        $this->render('password', 'account/password.twig');
    }

    protected function getAccountPages() {
        $user  = Session::getUser();
        $pages = Hooks::execute(Hooks::GET_ACCOUNT_PAGES, $user);

        foreach ($pages as $page) {
            if (!isset($page['order'])) $page['order'] = 999;
        }

        usort($pages, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        return array_values($pages);
    }

    protected function render($activePage, $template, array $data = array()) {
        $pages = $this->getAccountPages();

        if (!isset($data['DW_DOMAIN'])) {
            $this->setupHeaderVars($data, 'account');
        }

        $user = Session::getUser();

        foreach ($pages as $p) {
            // set title and adminactive if the controller code before us has not already set it
            if ($p['url'] === $activePage) {
                if (!isset($data['title'])) {
                    $data['title'] = $p['title'];
                }

                if (!isset($data['pages'])) {
                    $data['pages'] = $pages;
                }

                if (!isset($data['url'])) {
                    $data['url'] = $p['url'];
                }

                if (!isset($data['gravatar'])) {
                    $data['gravatar'] = md5(strtolower(trim($user->getEmail())));
                }

                if (!isset($data['user'])) {
                    $data['user'] = $user;
                }

                break;
            }
        }

        $this->getApp()->render($template, $data);
    }
}
