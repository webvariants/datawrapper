<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Plugin;
use Datawrapper\Hooks;

class DatawrapperPlugin_CoreVisOptions extends Plugin {
    public function init() {
        $plugin = $this;
        global $app;

        Hooks::register(
            Hooks::VIS_OPTION_CONTROLS,
            function($o, $k) use ($app, $plugin) {
                $env = array('option' => $o, 'key' => $k);
                $app->render('plugins/'.$plugin->getName().'/controls.twig', $env);
            }
        );

        Hooks::register(
            Hooks::VIS_OPTION_CONTROLS,
            function($o, $k) use ($app, $plugin) {
                $env = array('option' => $o, 'key' => $k);
                $app->render('plugins/'.$plugin->getName().'/colorselector.twig', $env);
            }
        );

        $this->declareAssets(array(
            'sync-controls.js',
            'sync-colorselector.js',
            'colorpicker.css'
        ), '|/chart/[^/]+/visualize|');
    }
}
