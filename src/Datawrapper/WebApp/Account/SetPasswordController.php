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
use Datawrapper\WebApp\AccountController;

class SetPasswordController extends AccountController {
    /**
     * this page shows up if an user has been invited to
     * datawrapper and therefor only needs to pick a password
     * to complete the registration process.
     */
    public function setAction($token) {
        $app   = $this->disableCache()->getApp();
        $pages = $this->getAccountPages();

        if (!empty($token)) {
            $users = ORM\UserQuery::create()->filterByActivateToken($token)->find();

            if (count($users) != 1) {
                $app->redirect('/?t=e&m='.__('This activation token is invalid. Your email address is probably already activated.'));
            }

            $page = array();
            add_header_vars($page, 'about');

            $page['salt'] = DW_AUTH_SALT;
            $app->render('account/set-password.twig', $page);
        }
        else {
            $app->notFound();
        }
    }
}
