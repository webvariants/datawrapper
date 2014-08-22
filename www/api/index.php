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
 * Datawrapper JSON API
 */

use Datawrapper\ORM\UserQuery;
use Datawrapper\Session;

////////////////////////////////////////////////////////////////////////////////////////////////////
// boot the main system

define('ROOT_PATH', dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR);
define('DW_VIEW', 'json');
define('DW_TOKEN_SALT', 'aVyyrmc2UpoZGJ3SthaKyGrFzaV3Z37iuFU4x5oLb_aKmhopz5md62UHn25Gf4ti');

require ROOT_PATH.'lib/bootstrap.php';

////////////////////////////////////////////////////////////////////////////////////////////////////
// helper functions

function error($code, $msg) {
    global $app;
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $result = array('status'=>'error');
    if (isset($code)) $result['code'] = $code;
    if (isset($msg)) $result['message'] = $msg;
    print json_encode($result);
}

function ok($data = null) {
    global $app;
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $result = array('status'=>'ok');
    if (isset($data)) $result['data'] = $data;
    print json_encode($result);
}

function get_user_ips() {
    $ips = array('remote_addr' => $_SERVER['REMOTE_ADDR']);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ips['x_forwared_for'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (isset($_SERVER['HTTP_CLIENT_IP'])) $ips['client_ip'] = $_SERVER['HTTP_CLIENT_IP'];
    return $ips;
}

function if_is_admin($callback) {
    $user = Session::getUser();
    if ($user->isAdmin()) {
        call_user_func($callback);
    } else {
        error('access-denied', 'need admin privileges.');
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// setup routing

$config = $dw_config;
$ns     = 'Datawrapper\\RestApp\\';

$app->get ('/account',                                    $ns.'AccountController:indexAction');
$app->get ('/account/lang',                               $ns.'AccountController:getLanguageAction');
$app->put ('/account/lang',                               $ns.'AccountController:updateLanguageAction');
$app->post('/account/reset-password',                     $ns.'AccountController:sendPasswordResetMailAction');
$app->put ('/account/reset-password',                     $ns.'AccountController:resetPasswordAction');
$app->post('/account/resend-activation',                  $ns.'AccountController:resendActivationAction');
$app->post('/account/resend-invitation',                  $ns.'AccountController:resendInvitationAction');
$app->post('/account/invitation/:token',                  $ns.'AccountController:validateInvitationAction');

// TODO: turn this into a sessions resource (POST /sessions = login, DELETE /sessions/mine = logout)
$app->post('/auth/login',                                 $ns.'AuthController:loginAction');
$app->get ('/auth/salt',                                  $ns.'AuthController:saltAction');
$app->post('/auth/logout',                                $ns.'AuthController:logoutAction');

$app->get   ('/charts',                                   $ns.'ChartController:indexAction');
$app->post  ('/charts',                                   $ns.'ChartController:createAction');
$app->get   ('/charts/:id',                               $ns.'ChartController:getAction');
$app->put   ('/charts/:id',                               $ns.'ChartController:updateAction');
$app->delete('/charts/:id',                               $ns.'ChartController:deleteAction');
$app->get   ('/charts/:id/data',                          $ns.'ChartController:getDataAction');
$app->put   ('/charts/:id/data',                          $ns.'ChartController:putDataAction');
$app->post  ('/charts/:id/data',                          $ns.'ChartController:postDataAction');
$app->post  ('/charts/:id/copy',                          $ns.'ChartController:copyAction');
$app->post  ('/charts/:id/publish',                       $ns.'ChartController:publishAction');
$app->get   ('/charts/:id/publish/status',                $ns.'ChartController:publishStatusAction');
$app->put   ('/charts/:id/thumbnail/:thumb',              $ns.'ChartController:putThumbnailAction');
$app->put   ('/charts/:id/store_snapshot',                $ns.'ChartController:snapshotAction');

$app->get   ('/gallery',                                  $ns.'GalleryController:indexAction');

$app->post  ('/jobs/:type/:id',                           $ns.'JobController:createAction');
$app->get   ('/jobs/:type/estimate',                      $ns.'JobController:estimateAction');
$app->put   ('/jobs/:id',                                 $ns.'JobController:updateAction');

$app->get   ('/organizations',                            $ns.'OrganizationController:indexAction');
$app->post  ('/organizations',                            $ns.'OrganizationController:createAction');
$app->put   ('/organizations/:id',                        $ns.'OrganizationController:updateAction');
$app->delete('/organizations/:id',                        $ns.'OrganizationController:deleteAction');
$app->post  ('/organizations/:id/users',                  $ns.'OrganizationController:addUserAction');
$app->delete('/organizations/:id/users/:uid',             $ns.'OrganizationController:removeUserAction');
$app->put   ('/organizations/:id/plugins/:op/:plugin_id', $ns.'OrganizationController:togglePermissionAction')->conditions(array('op' => '(remove|add|toggle|config)'));
$app->get   ('/organizations/:id/charts',                 $ns.'OrganizationController:chartsAction');

$app->put   ('/plugins/:id/:action'                       $ns.'PluginController:toggleAction');

$app->get   ('/products',                                 $ns.'ProductController:indexAction');
$app->post  ('/products',                                 $ns.'ProductController:createAction');
$app->put   ('/products/:id',                             $ns.'ProductController:updateAction');
$app->delete('/products/:id',                             $ns.'ProductController:deleteAction');
$app->post  ('/products/:id/plugins',                     $ns.'ProductController:addPluginAction');
$app->delete('/products/:id/plugins',                     $ns.'ProductController:removePluginAction');
$app->post  ('/products/:id/users',                       $ns.'ProductController:addToUsersAction');
$app->put   ('/products/:id/users',                       $ns.'ProductController:updateUsersAction');
$app->delete('/products/:id/users',                       $ns.'ProductController:deleteFromUsersAction');
$app->post  ('/products/:id/organizations',               $ns.'ProductController:addToOrganizationsAction');
$app->put   ('/products/:id/organizations',               $ns.'ProductController:updateOrganizationsAction');
$app->delete('/products/:id/organizations',               $ns.'ProductController:deleteFromOrganizationsAction');

$app->get   ('/themes',                                   $ns.'ThemeController:indexAction');
$app->get   ('/themes/:id',                               $ns.'ThemeController:getAction');

$app->get   ('/users',                                    $ns.'UserController:indexAction');
$app->post  ('/users',                                    $ns.'UserController:createAction');
$app->get   ('/users/:id',                                $ns.'UserController:getAction');
$app->put   ('/users/:id',                                $ns.'UserController:updateAction');
$app->delete('/users/:id',                                $ns.'UserController:deleteAction');
$app->post  ('/user/:id/products',                        $ns.'UserController:addProductsAction');

$app->get   ('/visualizations',                           $ns.'VisualizationController:indexAction');
$app->get   ('/visualizations/:id',                       $ns.'VisualizationController:getAction');

$pluginApiHooks = Hooks::execute(Hooks::PROVIDE_API);

if (!empty($pluginApiHooks)) {
    foreach ($pluginApiHooks as $hook) {
        if (!isset($hook['method'])) $hook['method'] = 'GET';
        $app->map('/plugin/' . $hook['url'], $hook['action'])->via($hook['method']);
    }
}

$app->notFound(function() {
    error('not-found', 'Not Found');
});

////////////////////////////////////////////////////////////////////////////////////////////////////
// go!

$app->run();
