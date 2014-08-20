<?php

use Datawrapper\ORM\ChartQuery;
use Datawrapper\Session;

function check_chart_readable($id, $callback = null) {
    $chart = ChartQuery::create()->findPK($id);

    if ($chart) {
        $user = Session::getUser();

        if ($chart->isReadable($user) === true) {
            if ($callback) {
                call_user_func($callback, $user, $chart);
            }
            return true;
        }
        else {
            // no such chart
            error_chart_not_writable();
            return false;
        }
    }
    else {
        // no such chart
        error_chart_not_found($id);
        return false;
    }
}

function check_chart_writable($id, $callback = null) {
    $chart = ChartQuery::create()->findPK($id);

    if ($chart) {
        $user = Session::getUser();

        if ($chart->isWritable($user) === true) {
            if ($callback) {
                call_user_func($callback, $user, $chart);
            }
            return true;
        }
        else {
            // no such chart
            error_chart_not_writable();
            return false;
        }
    }
    else {
        // no such chart
        error_chart_not_found($id);
        return false;
    }
}

function check_chart_public($id, $callback) {
    $chart = ChartQuery::create()->findPK($id);
    if ($chart) {
        $user = $chart->getUser();
        if ($user->isAbleToPublish()) {
            if ($chart->isPublic()) {
                call_user_func($callback, $user, $chart);
            } else if ($chart->_isDeleted()) {
                error_chart_deleted();
            } else {
                error_chart_not_published();
            }
        } else {
            // no such chart
            error_not_allowed_to_publish();
        }
    } else {
        // no such chart
        error_chart_not_found($id);
    }
}


function check_chart_exists($id, $callback) {
    $chart = ChartQuery::create()->findPK($id);
    if ($chart) {
        call_user_func($callback, $chart);
    } else {
        // no such chart
        error_chart_not_found($id);
    }
}
