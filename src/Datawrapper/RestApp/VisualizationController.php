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

use Datawrapper\Visualization;

/**
 * get list of all currently available chart types
 * watch out: this request involves browsing in the file
 * system and parsing of several JSON files
 *
 * it will be cached once per user session but should be
 * used carefully anyway. never call this in embedded charts
 */
class VisualizationController extends BaseController {
    public function indexAction() {
        if (false && isset($_SESSION['dw-visualizations'])) {
            // read from session cache
            // ToDo: use user-independend cache here (e.g. memcache)
            $res = $_SESSION['dw-visualizations'];
        } else {
            // read from file system
            $res = Visualization::all();
            // store in cache
            $_SESSION['dw-visualizations'] = $res;
        }
        ok($res);
    }

    public function getAction($visid) {
        if (false && isset($_SESSION['dw-visualizations-'.$visid])) {
            // read from session cache
            // ToDo: use user-independend cache here (e.g. memcache)
            $res = $_SESSION['dw-visualizations-'.$visid];
        } else {
            // read from file system
            $res = Visualization::get($visid);
            // store in cache
            $_SESSION['dw-visualizations-'.$visid] = $res;
        }
        ok($res);
    }
}
