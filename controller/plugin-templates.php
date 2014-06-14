<?php

use Datawrapper\ORM\PluginQuery;
use Datawrapper\Hooks;

/*
 * render templates provided by plugins
 */
$app->get('/plugins/:plugin/:template', function ($plugin_id, $template) use ($app) {
    disable_cache($app);

    if (PluginQuery::create()->isInstalled($plugin_id)) {
        if (file_exists(ROOT_PATH . 'templates/plugins/' . $plugin_id . '/' . $template)) {
            $app->render('plugins/' . $plugin_id . '/' . $template, array(
                'l10n__domain' => '/plugins/'.$plugin_id.'/...'
            ));
            return;
        }
    }
    $app->notFound();
});

// also execute plugin controller

Hooks::execute(Hooks::GET_PLUGIN_CONTROLLER, $app);
