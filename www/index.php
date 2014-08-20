<?php

use Datawrapper\ORM\UserQuery;
use Datawrapper\Hooks;
use Datawrapper\Session;

/**
 * Datawrapper main index
 *
 */

define('DATAWRAPPER_VERSION', '2.0.0-alpha');  // must match with package.json
define('ROOT_PATH', '../');

require_once ROOT_PATH . 'vendor/autoload.php';

check_server();

require ROOT_PATH . 'lib/bootstrap.php';

$twig = $app->view()->getEnvironment();
dwInitTwigEnvironment($twig);

$ns = 'Datawrapper\\WebApp\\';

$app->get ('/',                          $ns.'HomeController:indexAction');
$app->get ('/login',                     $ns.'LoginController:indexAction');
$app->get ('/setup',                     $ns.'SetupController:indexAction');
$app->post('/setup',                     $ns.'SetupController:setupAction');
$app->get ('/plugins/:plugin/:template', $ns.'PluginTemplatesController:templateAction');
$app->get ('/xhr/header/:page',          $ns.'XhrController:headerAction');
$app->get ('/xhr/home-login',            $ns.'XhrController:homeLoginAction');
$app->get ('/xhr/:chartid/vis-options',  $ns.'XhrController:visOptionsAction');

Hooks::execute(Hooks::GET_PLUGIN_CONTROLLER, $app);

require_once ROOT_PATH . 'controller/account.php';
require_once ROOT_PATH . 'controller/chart/create.php';
require_once ROOT_PATH . 'controller/chart/edit.php';
require_once ROOT_PATH . 'controller/chart/upload.php';
require_once ROOT_PATH . 'controller/chart/describe.php';
require_once ROOT_PATH . 'controller/chart/visualize.php';
require_once ROOT_PATH . 'controller/chart/data.php';
require_once ROOT_PATH . 'controller/chart/preview.php';
require_once ROOT_PATH . 'controller/chart/embed.php';
require_once ROOT_PATH . 'controller/chart/publish.php';
require_once ROOT_PATH . 'controller/chart/static.php';
require_once ROOT_PATH . 'controller/mycharts.php';
require_once ROOT_PATH . 'controller/admin.php';

$app->notFound(function() {
    error_not_found();
});

if ($dw_config['debug']) {
    $app->get('/phpinfo', function() use ($app) {
        phpinfo();
    });
}

/*
 * before processing any other route we check if the
 * user is not logged in and if prevent_guest_access is activated.
 * if both is true we redirect to /login
 */
$app->hook('slim.before.router', function () use ($app, $dw_config) {
    $user = Session::getUser();
    if (!$user->isLoggedIn() && !empty($dw_config['prevent_guest_access'])) {
        $req = $app->request();
        if (UserQuery::create()->filterByRole(array('admin', 'sysadmin'))->count() > 0) {
            if ($req->getResourceUri() != '/login' &&
                strncmp($req->getResourceUri(), '/account/invite/', 16) && // and doesn't start with '/account/invite/'
                strncmp($req->getResourceUri(), '/account/reset-password/', 24)) { // and doesn't start with '/account/reset-password/'
                $app->redirect('/login');
            }
        } else {
            if ($req->getResourceUri() != '/setup') {
                $app->redirect('/setup');
            }
        }
    }
});


/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This is responsible for executing
 * the Slim application using the settings and routes defined above.
 */

$app->run();

