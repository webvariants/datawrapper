<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Theme;
use Datawrapper\WebApp\AdminController;

class DatawrapperPlugin_AdminThemes_Controller extends AdminController {
    /**
     * controller for themes admin
     */
    public function themesAction() {
        $this->render('/themes', 'plugins/admin-themes/admin-themes.twig', array(
            'title'  => 'Themes',
            'themes' => Theme::all(),
            'count'  => $this->countChartsPerThemes()
        ));
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
