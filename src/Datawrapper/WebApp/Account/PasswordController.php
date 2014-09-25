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

use Datawrapper\WebApp\AccountController;

class PasswordController extends AccountController {
    public function formAction($token) {
        $this->disableCache();
        $this->render('password', 'account/password.twig');
    }
}
