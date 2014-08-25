<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\ORM;
use Datawrapper\Pagination;
use Datawrapper\PluginManager;
use Datawrapper\Session;
use Datawrapper\WebApp\AdminController;

class DatawrapperPlugin_AdminUsers_Controller extends AdminController {
    /**
     * controller for admin users
     */
    public function indexAction() {
        $app  = $this->disableCache()->getApp();
        $user = Session::getUser();
        $data = array(
            'q' => $app->request()->params('q', '')
        );

        $sort    = $app->request()->params('sort', '');
        $curPage = $app->request()->params('page', 0);
        $total   = $this->getQuery($user)->count();
        $perPage = 50;
        $append  = '';

        if ($data['q']) {
            $append = '&q='.$data['q'];
        }

        if (!empty($sort)) {
            $append .= '&sort='.$sort;
        }

        $data = Pagination::addVars($data, $total, $curPage, $perPage, $append);
        $data['users'] = $this->getQuery($user)->limit($perPage)->offset($curPage * $perPage)->find();

        $this->render('/users', 'plugins/admin-users/admin-users.twig', $data);
    }

    public function showAction($uid) {
        $app     = $this->disableCache()->getApp();
        $theUser = ORM\UserQuery::create()->findPk($uid);
        $data    = array(
            'title'       => 'Users » '.$theUser->guessName(),
            'the_user'    => $theUser,
            'userPlugins' => PluginManager::getUserPlugins($theUser->getId(), false),
        );

        $this->render('/users', 'plugins/admin-users/admin-user-detail.twig', $data);
    }

    protected function getQuery($user) {
        $app   = $this->getApp();
        $sort  = $app->request()->params('sort', '');
        $query = ORM\UserQuery::create()
            ->leftJoin('Datawrapper\ORM\User.Chart')
            ->withColumn('COUNT(Chart.Id)', 'NbCharts')
            ->groupBy('Datawrapper\ORM\User.Id')
            ->filterByDeleted(false);

        if ($app->request()->params('q')) {
            $query->filterByEmail('%'.$app->request()->params('q').'%');
        }

        if (!$user->isSysAdmin()) {
            $query->filterByRole('sysadmin', \Criteria::NOT_EQUAL);
        }

        switch ($sort) {
            case 'email':      $query->orderByEmail('asc');          break;
            case 'charts':     $query->orderBy('NbCharts', 'desc');  break;
            case 'created_at': $query->orderBy('createdAt', 'desc'); break;
        }

        return $query;
    }
}
