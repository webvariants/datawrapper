<?php

use Datawrapper\Application;
use Datawrapper\Hooks;
use Datawrapper\PluginManager;

/*
 * init Twig extensions and hooks
 */

function dwInitTwigEnvironment(Twig_Environment $twig) {
    $twig->setCache(ROOT_PATH.'/tmp/twig');
    $twig->enableAutoReload();
    $twig->addExtension(new Datawrapper\Twig\I18n\Extension());

    $twig->addFilter(new Twig_SimpleFilter('purify', function($dirty) {
        return Application::getInstance()->dw_htmlpurifier->purify($dirty);
    }));

    $twig->addFilter(new Twig_SimpleFilter('json', function($arr) {
        return json_encode($arr);
    }));

    $twig->addFunction(new Twig_SimpleFunction('hook', function() {
        call_user_func_array(array(Hooks::getInstance(), 'execute'), func_get_args());
    }));

    $twig->addFunction(new Twig_SimpleFunction('has_hook', function($hook) {
        return Hooks::getInstance()->hookRegistered($hook);
    }));

    $twig->addFunction(new Twig_SimpleFunction('has_plugin', function($plugin) {
        return PluginManager::loaded($plugin);
    }));

    return $twig;
}
