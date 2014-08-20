<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper;

class Mailer {
    protected $domain;
    protected $supportAddr;
    protected $logAddr;
    protected $errorAddr;

    public function __construct(array $config = null) {
        if ($config === null) {
            $config = $GLOBALS['dw_config'];
        }

        $this->domain      = $config['domain'];
        $this->supportAddr = $config['email']['support'];
        $this->logAddr     = $config['email']['log'];
        $this->errorAddr   = $config['email']['error'];
    }

    /**
     * use this to send email to our users
     */
    public function sendSupportMail($to, $subject, $message, array $replacements = array()) {
        // auto-replace support email address and domain
        if (empty($replacements['support_email'])) {
            $replacements['support_email'] = $this->supportAddr;
        }

        $replacements['domain'] = $this->domain;

        $subject = $this->replace($subject, $replacements);
        $message = $this->replace($message, $replacements);

        $this->executeSendHook(
            $to,
            $subject,
            $message,
            'From: noreply@'.$this->domain . "\r\n" .
            'Reply-To: '.$this->supportAddr . "\r\n" .
            'X-Mailer: PHP/' . phpversion()
        );
    }

    /**
     * send error message
     */
    public function sendErrorMail($subject, $message) {
        $this->executeSendHook($this->logAddr, $subject, $message, 'From: '.$this->errorAddr);
    }

    /**
     * Send email an email with attachements
     * @param $files - array of files to send
     *      ex: array(
     *              "my_image.png" => array(
     *                  "path" => "/home/datawrapper/my_image.png",
     *                  "format" => "image/png"
     *              )
     *          )
     *
     */
    function sendMailAttachment($to, $from, $subject, $body, array $files) {
        $random_hash = md5(date('r', time()));
           // $random_hash = md5(date('r', time()));
        $random_hash = '-----=' . md5(uniqid(mt_rand()));

        // headers
        $headers =  'From: '.$from."\n";
        // $headers .= 'Return-Path: <'.$email_reply.'>'."\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="'.$random_hash.'"';

        // message
        $message = 'This is a multi-part message in MIME format.'."\n\n";

        $message .= '--'.$random_hash."\n";
        $message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
        $message .= $body."\n\n";

        // attached files
        foreach ($files as $fn => $file) {
            $path   = $file['path'];
            $format = $file['format'];

            $attachment = chunk_split(base64_encode(file_get_contents($path)));

            $message .= '--'.$random_hash."\n";
            $message .= 'Content-Type: '. $format .'; name="'. $fn .'"'."\n";
            $message .= 'Content-Transfer-Encoding: base64'."\n";
            $message .= 'Content-Disposition:attachement; filename="'. $fn . '"'."\n\n";
            $message .= $attachment."\n";
        }

        $this->executeSendHook($to, $subject, $message, $headers);
    }

    /**
     * replaces %keys% in strings with the provided replacement
     *
     * e.g. dw_email_replace("Hello %name%!", array('name' => $user->getName()))
     */
    public function replace($body, $replacements) {
        foreach ($replacements as $key => $value) {
            $body = str_replace('%'.$key.'%', $value, $body);
        }

        return $body;
    }

    protected function executeSendHook($to, $subject, $message, $headers) {
        Hooks::execute(Hooks::SEND_EMAIL, $to, $subject, $message, $headers);
    }
}
