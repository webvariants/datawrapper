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

use Datawrapper\ORM\om\BasePluginData;

class PluginData extends BasePluginData {
    public function getData() {
        $data = parent::getData();
        return json_decode($data, true);
    }

    public function setData($data) {
        parent::setData(json_encode($data));
    }
}
