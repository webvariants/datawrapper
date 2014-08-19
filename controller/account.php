<?php

require_once ROOT_PATH . 'controller/account/settings.php';
require_once ROOT_PATH . 'controller/account/activate.php';
require_once ROOT_PATH . 'controller/account/set-password.php';
require_once ROOT_PATH . 'controller/account/reset-password.php';


function __init_account_pages() {
    global $app;

    $user = DatawrapperSession::getUser();
    $pages = DatawrapperHooks::execute(DatawrapperHooks::GET_ACCOUNT_PAGES, $user);

    foreach ($pages as $page) {
        if (!isset($page['order'])) $page['order'] = 999;
    }
    usort($pages, function($a, $b) { return $a['order'] - $b['order']; });

    $app->get('/account/?', function() use ($app, $pages) {
        $app->redirect('/account/' . $pages[0]['url']);
    });

    foreach ($pages as $page) {
        $context = array(
            'title' => $page['title'],
            'pages' => $pages,
            'active' => $page['url']
        );
        add_header_vars($context, 'account');
        $app->get('/account/' . $page['url'], $page['controller']($app, $context));
    }
}

__init_account_pages();