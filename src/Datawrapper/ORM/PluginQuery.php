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

use Datawrapper\ORM\om\BasePluginQuery;

class PluginQuery extends BasePluginQuery {
    public function isInstalled($plugin_id) {
        return count($this->filterByEnabled(true)->filterById($plugin_id)->find()) > 0;
    }
}
