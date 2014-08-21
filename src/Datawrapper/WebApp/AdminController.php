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

use Datawrapper\Hooks;
use Datawrapper\Session;

class AdminController extends BaseController {
    protected function getAdminPages() {
        $user  = Session::getUser();
        $pages = Hooks::execute(Hooks::GET_ADMIN_PAGES);

        foreach ($pages as $page) {
            if (!isset($page['order'])) $page['order'] = 999;
        }

        usort($pages, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        return array_values($pages);
    }

    protected function render($page, $template, array $data = array()) {
        $pages = $this->getAdminPages();

        if (!isset($data['DW_DOMAIN'])) {
            add_header_vars($data, 'admin');
        }

        $data['adminmenu'] = array();

        foreach ($pages as $p) {
            if ($p['url'] === $page) {
                $data['title']       = $p['title'];
                $data['adminactive'] = $p['url'];
            }

            // add admin pages to menu
            $data['adminmenu'][$p['url']] = $p['title'];
        }

        $this->getApp()->render($template, $data);
    }
}
