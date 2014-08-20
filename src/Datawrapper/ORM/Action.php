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

use Datawrapper\ORM\om\BaseAction;

class Action extends BaseAction {
    public static function logAction($user, $key, $details = null) {
        $action = new static();
        $action->setUser($user);
        $action->setKey($key);
        if (!empty($details)) {
            if (!is_numeric($details) && !is_string($details)) {
                $details = json_encode($details);
            }
            $action->setDetails($details);
        }
        $action->setActionTime(time());
        $action->save();
    }
}

