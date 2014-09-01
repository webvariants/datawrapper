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

use Datawrapper\ErrorPage;
use Datawrapper\ORM;
use Datawrapper\WebApp\AccountController;

class ResetPasswordController extends AccountController {
    public function resetAction($token) {
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
}
