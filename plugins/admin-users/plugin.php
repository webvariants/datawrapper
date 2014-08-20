<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Hooks;
use Datawrapper\ORM;
use Datawrapper\Plugin;
use Datawrapper\PluginManager;
use Datawrapper\Session;

class DatawrapperPlugin_AdminUsers extends Plugin {
    public function init() {
        $plugin = $this;
        // register plugin controller
        Hooks::register(
            Hooks::GET_ADMIN_PAGES,
            function() use ($plugin) {
                return array(
                    'url'        => '/users',
                    'title'      => __('Users', $plugin->getName()),
                    'controller' => array($plugin, 'users'),
                    'order'      => '2'
                );
            }
        );

        $this->declareAssets(
            array(
                'vendor/serious-toolkit/serious-widget.js',
                'dw.admin.users.js',
                'users.css'
            ),
            '|/admin/users|'
        );

        $user = Session::getUser();
        if ($user->isAdmin()) {
            $this->registerController(function($app) use ($plugin) {
                $app->get('/admin/users/:user_id', function($uid) use ($app, $plugin) {
                    $theUser = ORM\UserQuery::create()->findPk($uid);
                    $page = array(
                        'title' => 'Users » '.$theUser->guessName()
                    );
                    // manually add the admin nav menu vars
                    global $__dw_admin_pages;
                    foreach ($__dw_admin_pages as $adm_pg) {
                        $page['adminmenu'][$adm_pg['url']] = $adm_pg['title'];
                    }
                    add_header_vars($page, 'admin');
                    $page['the_user'] = $theUser;
                    $page['userPlugins'] = PluginManager::getUserPlugins($theUser->getId(), false);

                    $app->render('plugins/admin-users/admin-user-detail.twig', $page);
                });
            });
        }
    }

    /*
     * controller for admin users
     */
    public function users($app, $page) {
        $page = array_merge($page, array(
            'title' => __('Users'),
            'q' => $app->request()->params('q', '')
        ));
        $sort = $app->request()->params('sort', '');
        $user = Session::getUser();
        function getQuery($user) {
            global $app;
            $sort = $app->request()->params('sort', '');
            $query = ORM\UserQuery::create()
                ->leftJoin('Datawrapper\ORM\User.Chart')
                ->withColumn('COUNT(Chart.Id)', 'NbCharts')
                ->groupBy('Datawrapper\ORM\User.Id')
                ->filterByDeleted(false);
            if ($app->request()->params('q')) {
                $query->filterByEmail('%' . $app->request()->params('q') . '%');
            }
            if (!$user->isSysAdmin()) {
                $query->filterByRole('sysadmin', \Criteria::NOT_EQUAL);
            }
            switch ($sort) {
                case 'email': $query->orderByEmail('asc'); break;
                case 'charts': $query->orderBy('NbCharts', 'desc'); break;
                case 'created_at': $query->orderBy('createdAt', 'desc'); break;
            }
            return $query;
        }
        $curPage = $app->request()->params('page', 0);
        $total = getQuery($user)->count();
        $perPage = 50;
        $append = '';
        if ($page['q']) {
            $append = '&q=' . $page['q'];
        }
        if (!empty($sort)) {
            $append .= '&sort='.$sort;
        }
        add_pagination_vars($page, $total, $curPage, $perPage, $append);
        $page['users'] = getQuery($user)->limit($perPage)->offset($curPage * $perPage)->find();

        $app->render('plugins/admin-users/admin-users.twig', $page);
    }
}
