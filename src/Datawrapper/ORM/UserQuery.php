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

use Datawrapper\ORM\om\BaseUserQuery;
use Criteria;

class UserQuery extends BaseUserQuery {
    public function getUserByPwdResetToken($token) {
        $users = $this->filterByResetPasswordToken($token)->find();
        if (count($users) == 1) return $users[0];
        return false;
    }

    public function orderByChartCount($dir = Criteria::ASC) {
        return $this->orderBy('chartCount', $dir);
    }
}
