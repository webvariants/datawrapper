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
use Datawrapper\L10N;
use Datawrapper\Session;
use Datawrapper\SessionHandler\DatabaseHandler;
use Datawrapper\PluginManager;
use Datawrapper\HealthCheck;

// must match with package.json
define('DATAWRAPPER_VERSION', '2.0.0-alpha');

// if not done yet, include the autoloader
$loader = require_once ROOT_PATH.'vendor/autoload.php';

if (!defined('NO_SLIM')) {
    $requirements = new HealthCheck();
    $requirements->checkPulse();
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// load YAML parser and config

$dw_config = Spyc::YAMLLoad(ROOT_PATH.'config.yaml');
$dw_config = parse_config($dw_config);

if ($dw_config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// this salt is used to hash the passwords before sending them from the client via HTTP
if (!isset($dw_config['auth_salt'])) {
    $dw_config['auth_salt'] = 'uRPAqgUJqNuBdW62bmq3CLszRFkvq4RW';
}

define('DW_AUTH_SALT', $dw_config['auth_salt']);

////////////////////////////////////////////////////////////////////////////////////////////////////
// boot Propel

Propel::init(ROOT_PATH.'db/conf/datawrapper-conf.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
// init session

if (!defined('NO_SESSION')) {
    DatabaseHandler::initialize();
    Session::initSession();
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// init l10n

$locale = str_replace('-', '_', Session::getLanguage());
$domain = 'messages';
putenv('LANGUAGE='.$locale);
setlocale(LC_ALL, $locale);
setlocale(LC_TIME, $locale.'.utf8');

$__l10n = new L10N();
$__l10n->loadMessages($locale);

////////////////////////////////////////////////////////////////////////////////////////////////////
// init Slim

if (!defined('NO_SLIM')) {
    // Initialize Slim app...
    if (DW_VIEW === 'twig') {
        // ... with Twig-based templates for the webapp
        $app = new Application(array(
            'view'            => new Slim\Views\Twig(),
            'templates.path'  => ROOT_PATH.'templates',
            'session.handler' => null
        ));

        // setup our extensions and cache and stuff
        $view = $app->view()->getEnvironment();
        initTwigEnvironment($app->view()->getEnvironment());
    }
    else {
        $app = new Application();
    }

    $app->dw_classloader = $loader;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// connect to memcache

if (isset($dw_config['memcache'])) {
    $memcfg = $dw_config['memcache'];
    $memcache = new Memcache();
    $memcache->connect($memcfg['host'], $memcfg['port']) or die ("Could not connect");
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// load enabled plugins

PluginManager::load();
