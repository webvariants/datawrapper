<?php

use Datawrapper\Hooks;
use Datawrapper\PluginManager;

/*
 * init Twig extensions and hooks
 */

function dwGetHTMLPurifier() {
    static $instance = null;

    if (!$instance) {
        // Twig Extension to clean HTML from malicious code
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'a[href],p,b,div,span,strong,u,i,em,q,blockquote,*[style],br,small');
        $config->set('Cache.SerializerPath', ROOT_PATH.'/tmp/');

        $instance = new HTMLPurifier($config);
    }

    return $instance;
}

function dwInitTwigEnvironment(Twig_Environment $twig) {
    $twig->setCache(ROOT_PATH.'/tmp/twig');
    $twig->enableAutoReload();
    $twig->addExtension(new Datawrapper\Twig\I18n\Extension());

    $twig->addFilter(new Twig_SimpleFilter('purify', function($dirty) {
        return dwGetHTMLPurifier()->purify($dirty);
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
