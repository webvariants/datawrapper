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

use Datawrapper\ORM\JobQuery;

class JobController extends BaseController {
    /**
     * creates new job
     */
    public function createAction($type, $chart_id) {
        if_chart_is_writable($chart_id, function($user, $chart) use ($app, $type) {
            try {
                // create a new export job for this chart
                $params = json_decode($app->request()->getBody(), true);
                $job = JobQuery::create()->createJob($type, $chart, $user, $params);
                ok(ceil(JobQuery::create()->estimatedTime($type) / 60));
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * returns the estimated time to complete a new print job
     * in minutes
     */
    public function estimateAction($type) {
        ok(ceil(JobQuery::create()->estimatedTime($type) / 60));
    }

    /**
     * change status of a job, need admin access
     */
    public function updateAction($job_id) {
        if_is_admin(function() use ($app, $job_id) {
            $job = JobQuery::create()->findOneById($job_id);
            $params = json_decode($app->request()->getBody(), true);
            $job->setStatus($params['status']);
            $job->save();
            ok();
        });
    }
}
