<?php

use Datawrapper\Plugin;
use Datawrapper\Hooks;
use Datawrapper\Theme;

class DatawrapperPlugin_AdminThemes extends Plugin {
    public function init() {
        $plugin = $this;
        // register plugin controller
        Hooks::register(
            Hooks::GET_ADMIN_PAGES,
            function() use ($plugin) {
                return array(
                    'url'        => '/themes',
                    'title'      => __('Themes', $plugin->getName()),
                    'controller' => array($plugin, 'themesAdmin'),
                    'order'      => '3'
                );
            }
        );
    }

    /*
     * controller for themes admin
     */
    public function themesAdmin($app, $page) {
        $page = array_merge($page, array(
            'title'  => 'Themes',
            'themes' => Theme::all(),
            'count'  => count_charts_per_themes()
        ));
        $app->render('plugins/admin-themes/admin-themes.twig', $page);
    }
}
