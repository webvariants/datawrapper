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
    protected function getAccountPages() {
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
}

/*
foreach ($__dw_admin_pages as $admin_page) {
    $app->map('/admin' . $admin_page['url'], function() use ($app, $admin_page, $__dw_admin_pages) {
        disable_cache($app);

        $user = Session::getUser();

        if ($user->isAdmin()) {
            $page_vars = array(
                'title'       => $admin_page['title'],
                'adminmenu'   => array(),
                'adminactive' => $admin_page['url']
            );

            // add admin pages to menu
            foreach ($__dw_admin_pages as $adm_pg) {
                $page_vars['adminmenu'][$adm_pg['url']] = $adm_pg['title'];
            }

            add_header_vars($page_vars, 'admin');
            call_user_func_array($admin_page['controller'], array($app, $page_vars));
        }
        else {
            $app->notFound();
        }
    })->via('GET', 'POST');
}
 */
