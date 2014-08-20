<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp\Chart;

use Datawrapper\ORM;
use Datawrapper\Session;
use Datawrapper\WebApp\BaseController;

class CreateController extends BaseController {
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
}
