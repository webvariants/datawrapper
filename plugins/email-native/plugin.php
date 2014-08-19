<?php

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
