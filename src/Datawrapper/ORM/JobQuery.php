<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\ORM;

use Datawrapper\ORM\om\BaseJobQuery;

class JobQuery extends BaseJobQuery {
    /**
     * returns the estimated time to complete a new job
     */
    public function estimatedTime($type) {
        $avgTimePerJob = array(
            'export' => 5
        );
        $numJobsInQueue = $this->filterByType($type)->filterByStatus('queued')->count();
        return $numJobsInQueue * $avgTimePerJob[$type];
    }

    public function createJob($type, $chart, $user, $params) {
        $job = new Job();
        $job->setChartId($chart->getId());
        $job->setUserId($user->getId());
        $job->setCreatedAt(time());
        $job->setType($type);
        $job->setParameter(json_encode($params));
        $job->save();

        return $job;
    }
}
