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

use Datawrapper\ErrorPage;
use Datawrapper\Hooks;
use Datawrapper\ORM\ChartQuery;
use Datawrapper\Publishing\Publisher;
use Datawrapper\Session;
use qqFileUploader\Uploader;

class ChartController extends BaseController {
    /**
     * API: get list of all charts by the current user
     */
    public function indexAction() {
        $user = Session::getUser();
        if ($user->isLoggedIn()) {
            $filter = array();
            if ($app->request()->get('filter')) {
                $f = explode("|", $app->request()->get('filter'));
                foreach ($f as $e) {
                    list($key, $val) = explode(":", $e);
                    $filter[$key] = $val;
                }
            }
            $charts = ChartQuery::create()->getPublicChartsByUser($user, $filter, 0, 200, $app->request()->get('order'));
        } else {
            $charts = ChartQuery::create()->getGuestCharts();
        }
        $res = array();
        foreach ($charts as $chart) {
            $res[] = $app->request()->get('expand') ? $chart->serialize() : $chart->shortArray();
        }
        ok($res);
    }

    /**
     * API: create a new empty chart
     */
    public function createAction() {
        $user = Session::getUser();
        try {
            $chart = ChartQuery::create()->createEmptyChart($user);
            $result = array($chart->serialize());
            ok($result);
        } catch (Exception $e) {
            error('create-chart-error', $e->getMessage());
        }
    }

    /**
     * load chart meta data
     *
     * @param id chart id
     */
    public function getAction($id) {
        $chart = ChartQuery::create()->findPK($id);
        $user = Session::getUser();
        if (!empty($chart) && $chart->isReadable($user)) {
            ok($chart->serialize());
        } else {
            error('chart-not-found', 'No chart with that id was found');
        }
    }

    /**
     * check user and update chart meta data
     */
    public function updateAction($id) {
        if_chart_is_writable($id, function($user, $chart) use ($app) {
            $json = json_decode($app->request()->getBody(), true);
            $chart->unserialize($json);
            ok($chart->serialize());
        });
    }

    /**
     * API: get chart data
     *
     * @param chart_id chart id
     */
    public function getDataAction($chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app) {
            $data = $chart->loadData();
            $app->response()->header('Content-Type', 'text/csv;charset=utf-8');
            print $data;
        });
    }

    /**
     * API: upload data to a chart
     *
     * @param chart_id chart id
     */
    public function putDataAction($chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app) {
            $data = $app->request()->getBody();
            try {
                $filename = $chart->writeData($data);
                $chart->save();
                ok($filename);
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * API: upload csv file to a chart
     *
     * @param chart_id chart id
     */
    public function postDataAction($chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app) {

            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array('txt', 'csv', 'tsv');
            // max file size in bytes
            $sizeLimit = 2 * 1024 * 1024;

            $uploader = new Uploader($allowedExtensions, $sizeLimit);
            $result = $uploader->handleUpload('../../charts/data/tmp/');

            // to pass data through iframe you will need to encode all html tags
            $data = file_get_contents($uploader->filename);

            // check and correct file encoding
            function detect_encoding($string) {
              $list = array('utf-8', 'iso-8859-15', 'iso-8859-1', 'iso-8859-3', 'windows-1251');
              foreach ($list as $item) {
                try {
                    $sample = iconv($item, $item, $string);
                    if (md5($sample) == md5($string))
                        return $item;
                } catch (Exception $e) {}
              }
              return null;
            }
            $enc = detect_encoding($data); // works better than mb_detect_encoding($data);
            if (strtolower($enc) != "utf-8") {
                $data = mb_convert_encoding($data, "utf-8", $enc);
            }

            try {
                if ($result['success']) {
                    $filename = $chart->writeData($data);
                    $chart->save();
                    //echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
                    unlink($uploader->filename);
                    ok($result);
                } else {
                    error('upload-error', $result['error']);
                }
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }

        });
    });

    /**
     * delete chart
     */
    public function deleteAction($id) {
        if_chart_is_writable($id, function($user, $chart) use ($app) {
            $chart->setDeleted(true);
            $chart->setDeletedAt(time());
            $chart->setLastEditStep(3);
            $chart->save();
            $chart->unpublish();
            ok('');
        });
    }


    /**
     * API: copy/duplicate/fork a chart
     *
     * @param chart_id chart id
     */
    public function copyAction($chart_id) {
        if_chart_is_readable($chart_id, function($user, $chart) use ($app) {
            try {
                $copy = ChartQuery::create()->copyChart($chart);
                $copy->setUser(Session::getUser());
                $copy->save();
                ok(array('id' => $copy->getId()));
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    public function publishAction($chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app) {
            $publisher = $app->dw_publisher;

            $chart->publish();
            $publisher->publishChart($user, $chart);
            ok();
        });
    }

    public function publishStatusAction($chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app) {
            echo $app->dw_publisher->getStatus($chart);
        });
    }

    /**
     * stores client-side generated chart thumbnail
     */
    public function putThumbnailAction($chart_id, $thumb) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app, $thumb) {
            try {
                $imgurl = $app->request()->getBody();
                $imgdata = base64_decode(substr($imgurl, strpos($imgurl, ",") + 1));
                $static_path = Publisher::getStaticPath($chart);
                file_put_contents($static_path . "/" . $thumb . '.png', $imgdata);
                Hooks::execute(Hooks::PUBLISH_FILES, array(
                    array(
                        $static_path . "/" . $thumb . '.png',
                        $chart->getID() . '/' . $thumb . '.png',
                        'image/png'
                    )
                ));
                ok();
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * stores static snapshot of a chart (data, configuration, etc) as JSON
     * to /test/test-charts. This aims to simplify the generation of test
     * cases using the Datawrapper editor. Only for debugging.
     */
    public function snapshotAction($chart_id) {
        if (!empty($GLOBALS['dw_config']['debug_export_test_cases'])) {
            if_chart_exists($chart_id, function($chart) use ($app) {
                $json = $chart->serialize();
                $payload = json_decode($app->request()->getBody(), true);
                $name = $payload['id'];
                $json['_data'] = $chart->loadData();
                $json['_sig'] = $payload['signature'];
                if (empty($name)) {
                    error('', 'no name specified');
                } else {
                    $name = str_replace(" ", "-", $name);
                    $json['_id'] = $name;
                    file_put_contents("../../test/test-charts/" . $name . ".json", json_encode($json));
                    ok();
                }
            });
        }
    }

    /**
     * checks if a chart is writeable by the current user (or guest)
     *
     * @param chart_id
     * @param callback the function to be executed if chart is writable
     */
    protected function if_chart_is_writable($chart_id, $callback) {
        $chart = ChartQuery::create()->findPK($chart_id);
        if (!empty($chart)) {
            $user = Session::getUser();
            $res = $chart->isWritable($user);
            if ($res === true) {
                call_user_func($callback, $user, $chart);
            } else {
                error('access-denied', $res);
            }
        } else {
            error('no-such-chart', '');
        }
    }

    /**
     * checks if a chart is reable by the current user (or guest)
     *
     * @param chart_id
     * @param callback the function to be executed if chart is writable
     */
    protected function if_chart_is_readable($chart_id, $callback) {
        $chart = ChartQuery::create()->findPK($chart_id);
        if ($chart) {
            $user = Session::getUser();
            if ($chart->isReadable($user) === true) {
                call_user_func($callback, $user, $chart);
            } else {
                // no such chart
                ErrorPage::chartNotWritable();
            }
        } else {
            // no such chart
            ErrorPage::chartNotFound($id);
        }
    }

    protected function if_chart_exists($id, $callback) {
        $chart = ChartQuery::create()->findPK($id);
        if ($chart) {
            call_user_func($callback, $chart);
        } else {
            // no such chart
            error('no-such-chart', '');
        }
    }
}
