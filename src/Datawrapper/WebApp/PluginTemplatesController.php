<?php

namespace Datawrapper\WebApp;

use Datawrapper\ORM\PluginQuery;

class PluginTemplatesController extends BaseController {
    public function templateAction($plugin_id, $template) {
        $app = $this->getApp();

        disable_cache($app);

        if (PluginQuery::create()->isInstalled($plugin_id)) {
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
