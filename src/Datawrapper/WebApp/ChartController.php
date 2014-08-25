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

use Datawrapper\Hooks;
use Datawrapper\ORM;
use Datawrapper\Session;

class ChartController extends BaseController {
    /**
     * UPLOAD STEP
     */
    public function uploadAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $self = $this;

        ORM\Chart::ifIsWritable($chartID, function($user, $chart) use ($app, $self) {
            $page = array(
                'title'     => $chart->getID().' :: '.__('Upload Data'),
                'chartData' => $chart->loadData(),
                'chart'     => $chart,
                'datasets'  => Hooks::execute(Hooks::GET_DEMO_DATASETS)
            );

            add_header_vars($page, 'chart');
            $self->addEditorNav($page, 1);

            $res = $app->response();
            $res['Cache-Control'] = 'max-age=0';

            $app->render('chart/upload.twig', $page);
        });
    }

    /**
     * DESCRIBE STEP
     */
    public function describeAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $self = $this;

        ORM\Chart::ifIsWritable($chartID, function($user, $chart) use ($app, $self) {
            $page = array(
                'title'     => $chart->getID().' :: '.__('Check & Describe'),
                'chartData' => $chart->loadData(),
                'chart'     => $chart
            );

            add_header_vars($page, 'chart', 'chart-editor/describe.min.css');
            $self->addEditorNav($page, 2);

            switch (substr(Session::getLanguage(), 0, 2)) {
                case 'de':
                    $k = '.';
                    $d = ',';
                    break;

                case 'fr':
                    $k = ' ';
                    $d = ',';
                    break;

                default:
                    $k = ',';
                    $d = '.';
            }

            $page['columntypes'] = array(
                'text'   => 'Text',
                'number' => 'Number',
                'date'   => 'Date',
            );

            $page['numberformats'] = array(
                'n3' => '3 ('.number_format(1234.56789, 3, $d, $k).')',
                'n2' => '2 ('.number_format(1234.56789, 2, $d, $k).')',
                'n1' => '1 ('.number_format(1234.56789, 1, $d, $k).')',
                'n0' => '0 ('.number_format(1234.56789, 0, $d, $k).')'
            );

            $page['significantdigits'] = array(
                's6' => '6 ('.number_format(1234.56789, 2, $d, $k).')',
                's5' => '5 ('.number_format(123.456789, 2, $d, $k).')',
                's4' => '4 ('.number_format(12.34, 2, $d, $k).')',
                's3' => '3 ('.number_format(1.23, 2, $d, $k).')',
                's2' => '2 ('.number_format(0.12, 2, $d, $k).')',
                's1' => '1 ('.number_format(0.01, 2, $d, $k).')'
            );

            $app->render('chart/describe.twig', $page);
        });
    }

    /**
     * VISUALIZE STEP
     */
    public function visualizeAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $debug = $this->getConfig('debug_export_test_cases');
        $self  = $this;

        ORM\Chart::ifIsWritable($chartID, function($user, $chart) use ($app, $debug, $self) {
            $page = array(
                'title'               => $chart->getID().' :: '.__('Visualize'),
                'chartData'           => $chart->loadData(),
                'chart'               => $chart,
                'visualizations_deps' => Visualization::all('dependencies'),
                'visualizations'      => Visualization::all(),
                'vis'                 => Visualization::get($chart->getType()),
                'themes'              => Theme::all(),
                'theme'               => Theme::get($chart->getTheme()),
                'debug'               => $debug ? '1' : '0'
            );

            add_header_vars($page, 'chart');
            $self->addEditorNav($page, 3);

            $app->render('chart/visualize.twig', $page);
        });
    }

    /**
     * VISUALIZE STEP
     */
    public function staticAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $query = $app->request();

        ORM\Chart::ifIsPublic($chartID, function($user, $chart) use ($query) {
            $w      = $query->params('w') ?: 300;
            $h      = $query->params('h') ?: 220;
            $format = $query->params('f') ?: 'png';

            $out = $chart->getId().'-'.$w.'-'.$h.'.'.$format;

            $img_dir = ROOT_PATH.'charts/images/';
            $script  = ROOT_PATH.'scripts/render.js';
            $cmd     = PHANTOMJS.' '.$script.' '.$chart->getId().' '.$img_dir.$out.' '.$w.' '.$h;

            if ($format == 'png') {
                header('Content-Type: image/png');
            }
            else {
                $title = trim(strtolower($chart->getTitle()));
                $name  = $chart->getId().'-'.preg_replace('/[äöüa-z0-9ß]+/', '-', $title).'.pdf';

                header('Content-Disposition: attachment;filename="' . $name . '"');
                header('Content-Type: application/pdf');
            }

            if (!file_exists($img_dir.$out)) {
                exec($cmd);
            }

            $fp = fopen($img_dir.$out, 'rb');
            fpassthru($fp);
            exit;
        });
    }

    /**
     * PUBLISH STEP - shows progress of publishing action and thumbnail generation
     */
    public function publishAction($chartID) {
        $app     = $this->disableCache()->getApp();
        $phantom = !!$this->getConfig('phantomjs');
        $self    = $this;

        ORM\Chart::ifIsWritable($chartID, function($user, $chart) use ($app, $phantom, $self) {
            $chartActions = Hooks::execute(Hooks::GET_CHART_ACTIONS, $chart);

            // add duplicate action
            $chartActions[] = array(
                'id'    => 'duplicate',
                'icon'  => 'plus',
                'title' => __('Duplicate this chart'),
                'order' => 500
            );

            // sort actions
            usort($chartActions, function($a, $b) {
                return (isset($a['order']) ? $a['order'] : 999) - (isset($b['order']) ? $b['order'] : 999);
            });

            $page = array(
                'title'             => $chart->getID().' :: '.__('Publish'),
                'chartData'         => $chart->loadData(),
                'chart'             => $chart,
                'visualizations'    => Visualization::all(),
                'vis'               => Visualization::get($chart->getType()),
                'chartUrl'          => $chart->getPublicUrl(),
                'chartUrlLocal'     => '/chart/'.$chart->getID().'/preview',
                'themes'            => Theme::all(),
                'exportStaticImage' => $phantom,
                'chartActions'      => $chartActions,
                'estExportTime'     => ceil(ORM\JobQuery::create()->estimatedTime('export') / 60)
            );

            add_header_vars($page, 'chart', 'chart-editor/publish.min.css');
            $self->addEditorNav($page, 4);

            $app->render('chart/publish.twig', $page);
        });
    }

    /**
     * Shows a preview of a chart for display in an iFrame
     */
    public function previewAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $i18n = $this->getI18N();

        ORM\Chart::ifIsReadable($chartID, function($user, $chart) use ($app, $i18n) {
            if ($chart->getLanguage() != '') {
                $i18n->loadMessages($chart->getLanguage());
            }

            $page = get_chart_content($chart, $user, $app->request()->get('minify'), $app->request()->get('debug'));

            $page['plain']      = $app->request()->get('plain') == 1;
            $page['fullscreen'] = $app->request()->get('fs') == 1;
            $page['innersvg']   = $app->request()->get('innersvg') == 1;

            $app->render('chart.twig', $page);
        });
    }

    /**
     * Main controller for chart rendering
     */
    public function getAction($chartID) {
        $app  = $this->disableCache()->getApp();
        $i18n = $this->getI18N();

        ORM\Chart::ifIsPublic($chartID, function($user, $chart) use ($app, $i18n) {
            if ($chart->getLanguage() != '') {
                $i18n->loadMessages($chart->getLanguage());
            }

            $page = get_chart_content($chart, $user, $app->request()->get('minify') == 1);

            $page['thumb']      = $app->request()->params('t') == 1;
            $page['innersvg']   = $app->request()->get('innersvg') == 1;
            $page['plain']      = $app->request()->get('plain') == 1;
            $page['fullscreen'] = $app->request()->get('fs') == 1;

            $app->render('chart.twig', $page);
        });
    }

    public function createAction() {
        $app  = $this->disableCache()->getApp();
        $cfg  = $this->getConfig();
        $user = Session::getUser();

        if (!$user->isLoggedIn() && isset($cfg['prevent_guest_charts']) && $cfg['prevent_guest_charts']) {
            error_page('chart',
                __('Access denied.'),
                __('You need to be signed in.')
            );

            return;
        }

        $chart = ORM\ChartQuery::create()->createEmptyChart($user);
        $req   = $app->request();
        $step  = 'upload';

        if ($req->post('data') != null) {
            $chart->writeData($req->post('data'));

            $step = 'describe';

            if ($req->post('source-name') != null) {
                $chart->updateMetadata('describe.source-name', $req->post('source-name'));
                $step = 'visualize';
            }

            if ($req->post('source-url') != null) {
                $chart->updateMetadata('describe.source-url', $req->post('source-url'));
                $step = 'visualize';
            }

            if ($req->post('type') != null) {
                $chart->setType($req->post('type'));
            }

            if ($req->post('title') != null) {
                $chart->setTitle($req->post('title'));
            }
        }

        $chart->save();
        $app->redirect('/chart/'.$chart->getId().'/'.$step);
    }

    public function dataAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $res   = $app->response();
        $chart = ORM\ChartQuery::create()->findPK($chartID);

        $res['Cache-Control']       = 'max-age=0';
        $res['Content-Type']        = 'text/csv';
        $res['Content-Disposition'] = 'attachment; filename="datawrapper-'.$chartID.'.csv"';

        if ($chart) {
            print $chart->loadData();
        }
        else {
            error_chart_not_found($chartID);
        }
    }

    public function editAction($chartID) {
        $app = $this->disableCache()->getApp();

        ORM\Chart::ifExists($chartID, function($chart) use ($app) {
            $step = 'upload';

            switch ($chart->getLastEditStep()) {
                case 0:
                case 1:  $step = 'upload';   break;
                case 2:  $step = 'describe'; break;
                default: $step = 'visualize#tell-the-story';
            }

            $app->redirect('/chart/'.$chart->getId().'/'.$step);
        });
    }

    public function redirectAction($chartID) {
        $app->redirect('/chart/'.$chartID.'/');
    }

    public function nojsAction($chartID) {
        $app->redirect('/static/img/nojs.png');
    }

    public function addEditorNav(&$page, $step) {
        // define 4 step navigation
        $steps = array();
        $steps[] = array('index'=>1, 'id'=>'upload', 'title'=>__('Upload Data'));
        $steps[] = array('index'=>2, 'id'=>'describe', 'title'=>__('Check & Describe'));
        $steps[] = array('index'=>3, 'id'=>'visualize', 'title'=>__('Visualize'));
        $steps[] = array('index'=>4, 'id'=>'publish', 'title'=>__('Publish & Embed'));
        $page['steps'] = $steps;
        $page['chartLocale'] = $page['locale'];
        $page['metricPrefix'] = get_metric_prefix($page['chartLocale']);
        $page['createstep'] = $step;
    }
}
