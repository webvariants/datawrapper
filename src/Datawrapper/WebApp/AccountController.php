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

    public static function registerDefaultPages() {
        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Settings'),
                'order' => 5,
                'icon'  => 'fa-wrench',
                'url'   => 'settings'
            );
        });

        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Delete account'),
                'order' => 9999,
                'icon'  => 'fa-frown-o',
                'url'   => 'delete'
            );
        });

        Hooks::register(Hooks::GET_ACCOUNT_PAGES, function() {
            return array(
                'title' => __('Change password'),
                'order' => 10,
                'icon'  => 'fa-lock',
                'url'   => 'password'
            );
        });
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

    protected function render($activePage, $template, array $data = array()) {
        $pages = $this->getAccountPages();

        if (!isset($data['DW_DOMAIN'])) {
            $this->setupHeaderVars($data, 'account');
        }

        $user = Session::getUser();

        foreach ($pages as $p) {
            // set title and adminactive if the controller code before us has not already set it
            if ($p['url'] === $activePage) {
                if (!isset($data['title'])) {
                    $data['title'] = $p['title'];
                }

                if (!isset($data['pages'])) {
                    $data['pages'] = $pages;
                }

                if (!isset($data['url'])) {
                    $data['url'] = $p['url'];
                }

                if (!isset($data['gravatar'])) {
                    $data['gravatar'] = md5(strtolower(trim($user->getEmail())));
                }

                if (!isset($data['user'])) {
                    $data['user'] = $user;
                }

                break;
            }
        }

        $this->getApp()->render($template, $data);
    }
}
