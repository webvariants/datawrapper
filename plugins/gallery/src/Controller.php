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
use Datawrapper\ORM;
use Datawrapper\Pagination;
use Datawrapper\Plugin;
use Datawrapper\Session;
use Datawrapper\Visualization;
use Datawrapper\WebApp\BaseController;

class DatawrapperPlugin_Gallery_Controller extends BaseController {
    public function indexAction() {
        $this->showGallery(array(), array());
    }

    public function filterAction($key, $val) {
        $filter = array($key => $val);
        $data   = compact('key', 'val');

        $this->showGallery($filter, $data);
    }

    public function showGallery(array $filter, array $data) {
        $app     = $this->disableCache()->getApp();
        $user    = Session::getUser();
        $perPage = 12;
        $curPage = $app->request()->params('page') ?: 0;

        $charts = ORM\ChartQuery::create()->getGalleryCharts($filter, $curPage * $perPage, $perPage);
        $total  = ORM\ChartQuery::create()->countGalleryCharts($filter);

        $data = array_merge($data, array(
            'charts'  => $charts,
            'bymonth' => $this->nbChartsByMonth(),
            'byvis'   => $this->nbChartsByType()
        ));

        $data = Pagination::addVars($data, $total, $curPage, $perPage);
        $this->setupHeaderVars($data, 'gallery');

        $app->render('plugins/gallery/gallery.twig', $data);
    }

    protected function nbChartsByMonth() {
        $con = Propel::getConnection();
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c FROM chart WHERE show_in_gallery = 1 AND last_edit_step >= 4 and deleted = 0 GROUP BY ym ORDER BY ym DESC ;";
        $rs  = $con->query($sql);
        $res = array();
        $max = 0;

        foreach ($rs as $r) {
            $res[] = array('count' => $r['c'], 'id' => $r['ym'], 'name' => strftime('%B %Y', strtotime($r['ym'].'-01')));
            $max   = max($max, $r['c']);
        }

        foreach ($res as $c => $r) {
            $res[$c]['bar'] = round($r['count'] / $max * 80);
        }

        return $res;
    }

    protected function nbChartsByType() {
        $con = Propel::getConnection();
        $sql = "SELECT type, COUNT(*) c FROM chart WHERE show_in_gallery = 1 AND last_edit_step >= 4 and deleted = 0 GROUP BY type ORDER BY c DESC ;";
        $rs  = $con->query($sql);
        $res = array();
        $max = 0;

        foreach ($rs as $r) {
            $vis   = Visualization::get($r['type']);
            $lang  = substr(Session::getLanguage(), 0, 2);
            $res[] = array('count' => $r['c'], 'id' => $r['type'], 'name' => $vis['title']);
            $max   = max($max, $r['c']);
        }

        foreach ($res as $c => $r) {
            $res[$c]['bar'] = round($r['count'] / $max * 80);
        }

        return $res;
    }
}
