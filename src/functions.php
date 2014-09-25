<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Application;
use Datawrapper\Hooks;
use Datawrapper\PluginManager;

/**
 * adding translate function to global scope
 */
function __($text, $domain = false, $fallback = '') {
    global $__l10n;
    return $__l10n->translate($text, $domain, $fallback);
}

/**
 * parses the config and populates some defaults
 */
function parse_config(array $cfg) {
    // check that email adresses are set
    if (!isset($cfg['email']))
        $cfg['email'] = array();

    if (!isset($cfg['email']['support']))
        $cfg['email']['support'] = 'support@' . $cfg['domain'];

    if (!isset($cfg['email']['log']))
        $cfg['email']['log'] = 'admin@' . $cfg['domain'];

    if (!isset($cfg['email']['error']))
        $cfg['email']['error'] = 'error@' . $cfg['domain'];

    if (!isset($cfg['asset_domain']))
        $cfg['asset_domain'] = false;

    return $cfg;
}

function get_metric_prefix($locale) {
    switch (substr($locale, 0, 2)) {
        case 'de':
            $pre = array();
            $pre[3] = ' Tsd.';
            $pre[6] = ' Mio.';
            $pre[9] = ' Mrd.';
            $pre[12] = ' Bio.';
            return $pre;

        default:
            $pre = array();
            $pre[3] = 'k';
            $pre[6] = 'm';
            $pre[9] = 'b';
            $pre[12] = 't';
            return $pre;
    }
}

function disable_cache($app) {
    $res = $app->response();
    $res['Expires']       = 'Tue, 03 Jul 2001 06:00:00 GMT';
    $res['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0\npost-check=0, pre-check=0';
    $res['Pragma']        = 'no-cache';

    $app->lastModified(time()+1000);
}

function check_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function array_merge_recursive_simple() {
    if (func_num_args() < 2) {
        trigger_error(__FUNCTION__.' needs two or more array arguments', E_USER_WARNING);
        return;
    }

    $arrays = func_get_args();
    $merged = array();

    while ($arrays) {
        $array = array_shift($arrays);

        if (!is_array($array)) {
            trigger_error(__FUNCTION__.' encountered a non array argument', E_USER_WARNING);
            return;
        }

        if (!$array) {
            continue;
        }

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
                }
                else {
                    $merged[$key] = $value;
                }
            }
            else {
                $merged[] = $value;
            }
        }
    }

    return $merged;
}

/**
 * secure passwords with secure_auth_key, if configured
 */
function secure_password($pwd) {
    global $dw_config;

    if (isset($dw_config['secure_auth_key'])) {
        return hash_hmac('sha256', $pwd, $dw_config['secure_auth_key']);
    }
    else {
        return $pwd;
    }
}

/**
 * init Twig extensions and hooks
 */
function initTwigEnvironment(Twig_Environment $twig) {
    $twig->setCache(ROOT_PATH.'/tmp/twig');
    $twig->enableAutoReload();
    $twig->addExtension(new Datawrapper\Twig\I18n\Extension());

    $twig->addFilter(new Twig_SimpleFilter('purify', function($dirty) {
        return Application::getInstance()->dw_htmlpurifier->purify($dirty);
    }));

    $twig->addFilter(new Twig_SimpleFilter('json', function($arr) {
        return json_encode($arr);
    }));

    $twig->addFilter(new Twig_SimpleFilter('lettering', function($text) {
        $out = array();

        foreach (str_split($text) as $i => $char) {
            $out[] = '<span class="char'.$i.'">'.$char.'</span>';
        }

        return implode('', $out);
    }, array('is_safe' => array('html'))));

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
