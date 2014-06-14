<?php

use Datawrapper\Theme;

/*
 * get list of all currently available themes
 *
 */

$app->get('/themes', function() {
    $res = Theme::all();
    ok($res);
});

$app->get('/themes/:themeid', function($themeid) {
    $res = Theme::get($themeid);
    ok($res);
});
