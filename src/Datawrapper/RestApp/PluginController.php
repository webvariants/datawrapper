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

use Datawrapper\ORM\PluginQuery;

class PluginController extends BaseController {
    /**
     * change plugin status
     */
    public function toggleAction($plugin_id, $action) {
        if_is_admin(function() use ($plugin_id, $action) {
            $plugin = PluginQuery::create()->findPk($plugin_id);
            if ($plugin) {
                switch ($action) {
                    case 'enable': $plugin->setEnabled(true); break;
                    case 'disable': $plugin->setEnabled(false); break;
                    case 'publish': $plugin->setIsPrivate(false); break;
                    case 'unpublish': $plugin->setIsPrivate(true); break;
                }
                $plugin->save();
                ok();
            } else {
                error('plugin-not-found', 'No plugin found with that ID');
            }
        });
    }
}
