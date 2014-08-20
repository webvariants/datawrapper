<?php

/*
 * bootstrap.php
 */

use Datawrapper\L10N;
use Datawrapper\Session;
use Datawrapper\SessionHandler\DatabaseHandler;
use Datawrapper\PluginManager;
use Slim\Slim;

// if not done yet, include the autoloader
require_once ROOT_PATH . 'vendor/autoload.php';

// load YAML parser and config
$dw_config = Spyc::YAMLLoad(ROOT_PATH . 'config.yaml');
$dw_config = parse_config($dw_config);

if ($dw_config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Include the main Propel script
// Initialize Propel with the runtime configuration
Propel::init(ROOT_PATH . 'db/build/conf/datawrapper-conf.php');

// this salt is used to hash the passwords in database
if (!isset($dw_config['auth_salt'])) $dw_config['auth_salt'] = 'uRPAqgUJqNuBdW62bmq3CLszRFkvq4RW';
define('DW_AUTH_SALT', $dw_config['auth_salt']);

/*
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

if (!defined('NO_SESSION')) {
    DatabaseHandler::initialize();
    Session::initSession();
}

// init l10n
$locale = str_replace('-', '_', Session::getLanguage());
$domain = 'messages';
putenv('LANGUAGE=' . $locale);
setlocale(LC_ALL, $locale);
setlocale(LC_TIME, $locale.'.utf8');

$__l10n = new L10N();
$__l10n->loadMessages($locale);

if (!defined('NO_SLIM')) {
    // Initialize Slim app..
    if (ROOT_PATH == '../') {
        $app = new Slim(array(
            'view' => new TwigView(),
            'templates.path' => '../templates',
            'session.handler' => null
        ));
    } else {
        // ..or with JSONView for API.
        $app = new Slim(array( 'view' => 'JSONView' ));
    }
}

if (isset($dw_config['memcache'])) {
    $memcfg = $dw_config['memcache'];
    $memcache = new Memcache();
    $memcache->connect($memcfg['host'], $memcfg['port']) or die ("Could not connect");
}

PluginManager::load();
