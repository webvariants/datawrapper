<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\WebApp;

use Datawrapper\ORM;

class PluginTemplatesController extends BaseController {
    public function templateAction($plugin_id, $template) {
        $app = $this->disableCache()->getApp();

        if (ORM\PluginQuery::create()->isInstalled($plugin_id)) {
            if (file_exists(ROOT_PATH.'templates/plugins/'.$plugin_id.'/'.$template)) {
                $app->render('plugins/'.$plugin_id.'/'.$template, array(
                    'l10n__domain' => '/plugins/'.$plugin_id.'/...'
                ));

                return;
            }
        }

        $app->notFound();
    }
}
