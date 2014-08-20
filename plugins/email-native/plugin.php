<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Plugin;
use Datawrapper\Hooks;

class DatawrapperPlugin_EmailNative extends Plugin {
    public function init() {
        Hooks::register(Hooks::SEND_EMAIL, array($this, 'sendMail'));
    }

    /**
     * Send an email
     */
    function sendMail($to, $subject, $body, $headers = '') {
        if (empty($headers)) {
            $headers = 'From: noreply@'.$GLOBALS['dw_config']['domain'];
        }

        return mail($to, $subject, $body, $headers);
    }
}
