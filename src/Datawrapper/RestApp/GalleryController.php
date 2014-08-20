<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\RestApp;

use Datawrapper\ORM\ChartQuery;

class GalleryController extends BaseController {
    /**
     * returns the metadata for all charts that are allowed
     * to show in the gallery
     */
    public function indexAction() {
        $result = array();
        $q = ChartQuery::create()
            ->filterByShowInGallery(true)
            ->filterByLastEditStep(array('min' => 4))
            ->orderByCreatedAt('desc');
        if ($app->request()->get('type')) {
            $q->filterByType($app->request()->get('type'));
        }
        if ($app->request()->get('theme')) {
            $q->filterByTheme($app->request()->get('theme'));
        }
        if ($app->request()->get('month')) {
            $q->filterByTheme($app->request()->get('theme'));
        }
        $charts = $q->limit(20)->find();
        foreach ($charts as $chart) {
            $result[] = $chart->toArray();
        }
        ok($result);
    }
}
