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

class AccountController extends BaseController {
    public function redirectAction() {
        $app   = $this->getApp();
        $pages = $this->getAccountPages();
        $first = reset($pages);

        $app->redirect('/account/'.$first['url']);
    }

    protected function getAccountPages() {
        $user  = Session::getUser();
        $pages = Hooks::execute(Hooks::GET_ACCOUNT_PAGES, $user);

        foreach ($pages as $page) {
            if (!isset($page['order'])) $page['order'] = 999;
        }

        usort($pages, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        return array_values($pages);
    }

    protected function render($page, $template, array $data = array()) {
        $pages = $this->getAccountPages();

        if (!isset($data['DW_DOMAIN'])) {
            add_header_vars($data, 'account');
        }

        foreach ($pages as $p) {
            if ($p['url'] === $page) {
                $data['title'] = $p['title'];
                $data['pages'] = $pages;
                $data['url']   = $p['url'];
            }
        }

        $this->getApp()->render($template, $data);
    }
}
