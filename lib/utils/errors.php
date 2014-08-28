<?php

function error_page($step, $title, $message, $options = false, $status = 500) {
    global $app;

    $tmpl = array(
        'title'   => $title,
        'message' => $message,
        'options' => $options,
    );

    $app->status($status);
    add_header_vars($tmpl, $step);
    $app->render('error.twig', $tmpl);
}

function error_chart_not_published() {
    error_page('chart',
        __('Hold on!'),
        __('Sorry, but it seems that the chart you want to see is not quite ready for the world, yet. Why don\'t you just relax and wait a minute?'),
        false,
        404
    );
}

function error_chart_deleted() {
    error_page('chart',
        __('Too late'),
        __('Sorry, but it seems that the chart you want to see has already passed away because its author decided to delete it.'),
        false,
        404
    );
}

function error_not_allowed_to_publish() {
    error_page('chart',
        __('Whoops! You\'re not allowed to publish charts, yet'),
        __('Sorry, but it seems that your account is not ready to publish charts, yet.'),
        array(
            __('If you created the chart as a guest, you should <a href="#login">sign up for a free account</a> now. In case you already did that, you probably still need to activate you e-mail address by clicking on that activation link we sent you.')
        ),
        403
    );
}

function error_chart_not_found($id) {
    error_page('chart',
        __('Whoops! We couldn\'t find that chart..'),
        __('Sorry, but it seems that there is no chart with the id <b>'.$id.'</b> (anymore)'),
        false, 404
    );
}

function error_chart_not_writable() {
    error_page('chart',
        __('Whoops! That charts doesn\'t belong to you'),
        __('Sorry, but the requested chart belongs to someone else.'),
        array(
            __('Please check if you\'re logged in.')
        ),
        403
    );
}
