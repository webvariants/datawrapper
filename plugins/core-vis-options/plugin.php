<?php

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
