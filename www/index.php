<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * Datawrapper Web App
 */

use Datawrapper\ErrorPage;
use Datawrapper\Hooks;
use Datawrapper\ORM\UserQuery;
use Datawrapper\Session;

////////////////////////////////////////////////////////////////////////////////////////////////////
// boot the main system

define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
define('DW_VIEW', 'twig');

require ROOT_PATH.'src/bootstrap.php';

////////////////////////////////////////////////////////////////////////////////////////////////////
// setup routing

$ns = 'Datawrapper\\WebApp\\';

$app->get ('/',                                       $ns.'HomeController:indexAction');

$app->get ('/login',                                  $ns.'LoginController:indexAction');

$app->get ('/setup',                                  $ns.'SetupController:indexAction');
$app->post('/setup',                                  $ns.'SetupController:setupAction');

$app->get ('/plugins/:plugin/:template',              $ns.'PluginTemplatesController:templateAction');

$app->get ('/xhr/header/:page',                       $ns.'XhrController:headerAction');
$app->get ('/xhr/home-login',                         $ns.'XhrController:homeLoginAction');
$app->get ('/xhr/:chartid/vis-options',               $ns.'XhrController:visOptionsAction');

$app->get ('/mycharts(/?|/by/:key/:val)',             $ns.'MyChartsController:mychartsAction');
$app->get ('/admin/charts/:userid(/?|/by/:key/:val)', $ns.'MyChartsController:adminAction');

$app->get ('/account/?',                              $ns.'AccountController:redirectAction');
$app->get ('/account/settings',                       $ns.'Account\SettingsController:settingsAction');
$app->get ('/account/activate/:token',                $ns.'Account\ActivateController:activateAction');
$app->get ('/account/invite/:token',                  $ns.'Account\ActivateController:inviteAction');
$app->post('/account/invite/:token',                  $ns.'Account\ActivateController:doInviteAction');
$app->post('/account/reset-password/:token',          $ns.'Account\ResetPasswordController:resetAction');
$app->post('/account/set-password/:token',            $ns.'Account\SetPasswordController:setAction');

$app->map ('/chart/create',                           $ns.'ChartController:createAction')->via('GET', 'POST');
$app->get ('/chart/:id',                              $ns.'ChartController:redirectAction');
$app->get ('/chart/:id/',                             $ns.'ChartController:getAction');
$app->get ('/chart/:id/data(\.csv)?',                 $ns.'ChartController:dataAction');
$app->get ('/chart/:id/describe',                     $ns.'ChartController:describeAction');
$app->get ('/chart/:id/edit',                         $ns.'ChartController:editAction');
$app->get ('/chart/:id/preview/?',                    $ns.'ChartController:previewAction');
$app->get ('/chart/:id/nojs.png',                     $ns.'ChartController:nojsAction');
$app->get ('/chart/:id/publish',                      $ns.'ChartController:publishAction');
$app->get ('/chart/:id/static',                       $ns.'ChartController:staticAction');
$app->get ('/chart/:id/upload',                       $ns.'ChartController:uploadAction');
$app->get ('/chart/:id/visualize',                    $ns.'ChartController:visualizeAction');

Hooks::execute(Hooks::GET_PLUGIN_CONTROLLER, $app);

// provide the first, always available account page
Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
    return array(
        'title' => __('Settings'),
        'order' => 5,
        'icon'  => 'fa-wrench',
        'url'   => 'settings'
    );
});

$app->notFound(function() {
    ErrorPage::show('',
        __('404 - Page not found'),
        __('The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly. If all else fails, you can visit our home page at the link below.')
    );
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

////////////////////////////////////////////////////////////////////////////////////////////////////
// go!

$app->run();
