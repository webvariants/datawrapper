<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp;

use \Propel;
use Datawrapper\ORM;
use Datawrapper\Pagination;
use Datawrapper\Session;
use Datawrapper\Theme;
use Datawrapper\Visualization;

class MyChartsController extends BaseController {
    public function mychartsAction($key = false, $val = false) {
        $app  = $this->disableCache()->getApp();
        $user = Session::getUser();

        if ($user->isLoggedIn()) {
            $this->userCharts($app, $user, $key, $val);
        }
        else {
            error_page('mycharts',
                __('Whoops! You need to be logged in.'),
                __('Good news is, sign up is free and takes less than 20 seconds.')
            );
        }
    }

    public function adminAction($userid, $key = false, $val = false) {
        $app  = $this->disableCache()->getApp();
        $user = Session::getUser();

        if ($user->isAdmin()) {
            $user2 = UserQuery::create()->findOneById($userid);

            if ($user2) {
                $this->userCharts($app, $user2, $key, $val);
            }
            else {
                error_page('mycharts',
                    __('User not found!'),
                    __('There is no user with the given user id.')
                );
            }
        }
        else {
            $app->notFound();
        }
    }

    /**
     * shows MyChart page for a given user, which is typically the
     * logged user, but admins can view others MyCharts page, too.
     */
    protected function userCharts($app, $user, $key, $val) {
        $curPage = $app->request()->params('page');
        $q       = $app->request()->params('q');

        if (empty($curPage)) {
            $curPage = 0;
        }

        $perPage = 12;
        $filter  = !empty($key) ? array($key => $val) : array();

        if (!empty($q)) {
            $filter['q'] = $q;
        }

        $charts = ORM\ChartQuery::create()->getPublicChartsByUser($user, $filter, $curPage * $perPage, $perPage);
        $total  = ORM\ChartQuery::create()->countPublicChartsByUser($user, $filter);

        $page = array(
            'title'         => __('My Charts'),
            'charts'        => $charts,
            'bymonth'       => $this->nbChartsByMonth($user),
            'byvis'         => $this->nbChartsByType($user),
            'bylayout'      => $this->nbChartsByLayout($user),
            'bystatus'      => $this->nbChartsByStatus($user),
            'key'           => $key,
            'val'           => $val,
            'search_query'  => empty($q) ? '' : $q,
            'mycharts_base' => '/mycharts'
        );

        $curUser = Session::getUser();

        if ($curUser->isAdmin() && $curUser != $user) {
            $page['user2']         = $user;
            $page['mycharts_base'] = '/admin/charts/'.$user->getId();
            $page['all_users']     = ORM\UserQuery::create()->filterByDeleted(false)->orderByEmail()->find();
        }

        $this->setupHeaderVars($page, 'mycharts');
        $page = Pagination::addVars($page, $total, $curPage, $perPage, empty($q) ? '' : '&q='.$q);

        $app->render('mycharts.twig', $page);
    }

    protected function nbChartsByMonth($user) {
        $con = Propel::getConnection();
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c FROM chart WHERE author_id = ".$user->getId()." AND deleted = 0 AND last_edit_step >= 2 GROUP BY ym ORDER BY ym DESC";
        $rs  = $con->query($sql);
        $res = array();

        foreach ($rs as $r) {
            $res[] = array('count' => $r['c'], 'id' => $r['ym'], 'name' => strftime('%B %Y', strtotime($r['ym'].'-01')));
        }

        return $res;
    }

    protected function nbChartsByType($user) {
        $con = Propel::getConnection();
        $sql = "SELECT type, COUNT(*) c FROM chart WHERE author_id = ".$user->getId()." AND deleted = 0 AND last_edit_step >= 2 GROUP BY type ORDER BY c DESC";
        $rs  = $con->query($sql);
        $res = array();

        foreach ($rs as $r) {
            $vis  = Visualization::get($r['type']);
            $lang = substr(Session::getLanguage(), 0, 2);

            if (!isset($vis['title'])) continue;
            if (empty($vis['title'][$lang])) $lang = 'en';

            $res[] = array('count' => $r['c'], 'id' => $r['type'], 'name' => $vis['title']);
        }

        return $res;
    }

    protected function nbChartsByLayout($user) {
        $con = Propel::getConnection();
        $sql = "SELECT theme, COUNT(*) c FROM chart WHERE author_id = ".$user->getId()." AND deleted = 0 AND last_edit_step >= 2 GROUP BY theme ORDER BY c DESC";
        $rs  = $con->query($sql);
        $res = array();

        foreach ($rs as $r) {
            $theme = Theme::get($r['theme']);
            if (!$theme) continue; // ignoring charts whose themes have been removed
            $res[] = array('count' => $r['c'], 'id' => $r['theme'], 'name' => $theme['title']);
        }

        return $res;
    }

    protected function nbChartsByStatus($user) {
        $published = ORM\ChartQuery::create()->filterByUser($user)->filterByDeleted(false)->filterByLastEditStep(array('min' => 4))->count();
        $draft     = ORM\ChartQuery::create()->filterByUser($user)->filterByDeleted(false)->filterByLastEditStep(3)->count();

        return array(
            array('id' => 'published', 'name' => __('Published'), 'count' => $published),
            array('id' => 'draft',     'name' => __('Draft'),     'count' => $draft)
        );
    }
}
