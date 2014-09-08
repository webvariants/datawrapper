<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\Plugin\AdminDashboard;

use Datawrapper\ORM;
use Datawrapper\Session;
use Datawrapper\Visualization;
use Datawrapper\WebApp\AdminController;

class Controller extends AdminController {
    /**
     * controller for admin dashboard
     */
    public function dashboardAction() {
        $con = \Propel::getConnection();
        $data = array();

        $publised_sql      = 'SELECT DATE_FORMAT(published_at, \'%Y-%m-%d\') pub_date, COUNT(*) pub_count FROM `chart` WHERE last_edit_step = 5 GROUP BY pub_date ORDER BY `pub_date` DESC LIMIT 1,90';
        $publised_week_sql = 'SELECT DATE_FORMAT(published_at, \'%Y-w%u\') pub_date, COUNT(*) pub_count FROM `chart` WHERE last_edit_step = 5 GROUP BY pub_date ORDER BY `pub_date` DESC LIMIT 1,26';
        $user_signups_sql  = 'SELECT DATE_FORMAT(created_at, \'%Y-%m-%d\') create_date, COUNT(*) user_count FROM `user` GROUP BY create_date ORDER BY `create_date` DESC LIMIT 1,90';

        $numUsers          = ORM\UserQuery::create()->filterByDeleted(false)->count();
        $numUsersPending   = ORM\UserQuery::create()->filterByDeleted(false)->filterByRole(ORM\UserPeer::ROLE_PENDING)->count();
        $numUsersActivated = ORM\UserQuery::create()->filterByDeleted(false)->filterByRole(ORM\UserPeer::ROLE_EDITOR)->count();
        $numUsersDeleted   = ORM\UserQuery::create()->filterByDeleted(true)->count();
        $users_csv         = "Type;Count\\nPending;$numUsersPending\\nActivated;$numUsersActivated\\nDeleted;$numUsersDeleted";

        $numCharts          = ORM\ChartQuery::create()->filterByDeleted(false)->count();
        $numChartsUpload    = ORM\ChartQuery::create()->filterByLastEditStep(array('max' => 1))->filterByDeleted(false)->count();
        $numChartsDescribe  = ORM\ChartQuery::create()->filterByLastEditStep(2)->filterByDeleted(false)->count();
        $numChartsVisualize = ORM\ChartQuery::create()->filterByLastEditStep(3)->filterByDeleted(false)->count();
        $numChartsPublished = ORM\ChartQuery::create()->filterByLastEditStep(array('min' => 4))->filterByDeleted(false)->count();
        $charts_csv         = "LastEditStep;Count\\nUpload;$numChartsUpload\\nDescribe;$numChartsDescribe\\nVisualize;$numChartsVisualize\\nPublish;$numChartsPublished\\n";

        $charts_by_type_csv = $this->res2csv($con->query('SELECT type, COUNT(*) FROM chart WHERE deleted = 0 GROUP BY type;'));
        $charts_by_type_csv = str_replace('-chart', '', $charts_by_type_csv);

        $data = array(
            'num_users'            => $numUsers,
            'num_users_activated'  => $numUsersActivated,
            'num_charts'           => $numCharts,
            'num_charts_published' => $numChartsPublished,
            'published_csv'        => $this->res2csv($con->query($publised_sql)),
            'published_week_csv'   => $this->res2csv($con->query($publised_week_sql)),
            'users_csv'            => $users_csv,
            'charts_edit_step_csv' => $charts_csv,
            'charts_by_type_csv'   => $charts_by_type_csv,
            'created_csv'          => $this->res2csv($con->query('SELECT DATE_FORMAT(created_at, \'%Y-%m-%d\') pub_date, COUNT(*) pub_count FROM `chart` GROUP BY pub_date ORDER BY `pub_date` DESC LIMIT 1,90')),
            'created_weekly_csv'   => $this->res2csv($con->query('SELECT DATE_FORMAT(created_at, \'%Y-w%u\') pub_date, COUNT(*) pub_count FROM `chart` GROUP BY pub_date ORDER BY `pub_date` DESC LIMIT 1,26')),
            'user_signups_csv'     => $this->res2csv($con->query($user_signups_sql)),
            'linechart'            => Visualization::get('line-chart'),
            'columnchart'          => Visualization::get('column-chart'),
            'donutchart'           => Visualization::get('donut-chart'),
            'chartLocale'          => 'en-US'
        );

        $this->render('/', 'plugins/admin-dashboard/admin-dashboard.twig', $data);
    }

    // returns a CSV from a MySQL resultset
    protected function res2csv($rs) {
        $csv = "";
        $keys = array();
        $results = array();
        foreach ($rs as $r) {
            if (count($keys) == 0) {
                foreach ($r as $key => $val) {
                    if (is_string($key)) $keys[] = $key;
                }
                $csv = implode(";", $keys)."\\n";
            }
            $results[] = $r;
        }
        $results = array_reverse($results);
        foreach ($results as $r) {
            $values = array();
            foreach ($keys as $key) {
                $values[] = $r[$key];
            }
            $csv .=  implode(";", $values)."\\n";
        }
        return $csv;
    }
}
