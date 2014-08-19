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
            'count'  => $this->countChartsPerThemes()
        ));
        $app->render('plugins/admin-themes/admin-themes.twig', $page);
    }

    protected function countChartsPerThemes() {
        $con = Propel::getConnection();
        $sql = "SELECT theme, COUNT(*) c FROM chart WHERE deleted = 0 GROUP BY theme;";
        $res = $con->query($sql);
        $ret = array();

        foreach ($res as $r) {
            $ret[$r['theme']] = $r['c'];
        }

        return $ret;
    }
}
