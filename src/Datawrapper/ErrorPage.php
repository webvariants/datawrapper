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

class ErrorPage {
    public static function show($step, $title, $message, $options = false, $status = 500) {
        $app  = Application::getInstance();
        $tmpl = array(
            'title'   => $title,
            'message' => $message,
            'options' => $options,
        );

        $app->status($status);
        $app->dw_header->addVars($tmpl, $step);
        $app->render('error.twig', $tmpl);
    }

    public static function chartNotPublished() {
        self::show('chart',
            __('Hold on!'),
            __('Sorry, but it seems that the chart you want to see is not quite ready for the world, yet. Why don\'t you just relax and wait a minute?'),
            false,
            404
        );
    }

    public static function chartDeleted() {
        self::show('chart',
            __('Too late'),
            __('Sorry, but it seems that the chart you want to see has already passed away because its author decided to delete it.'),
            false,
            404
        );
    }

    public static function notAllowedToPublish() {
        self::show('chart',
            __('Whoops! You\'re not allowed to publish charts, yet'),
            __('Sorry, but it seems that your account is not ready to publish charts, yet.'),
            array(
                __('If you created the chart as a guest, you should <a href="#login">sign up for a free account</a> now. In case you already did that, you probably still need to activate you e-mail address by clicking on that activation link we sent you.')
            ),
            403
        );
    }

    public static function chartNotFound($id) {
        self::show('chart',
            __('Whoops! We couldn\'t find that chart..'),
            __('Sorry, but it seems that there is no chart with the id <b>'.$id.'</b> (anymore)'),
            false, 404
        );
    }

    public static function chartNotWritable() {
        self::show('chart',
            __('Whoops! That charts doesn\'t belong to you'),
            __('Sorry, but the requested chart belongs to someone else.'),
            array(
                __('Please check if you\'re logged in.')
            ),
            403
        );
    }
}
