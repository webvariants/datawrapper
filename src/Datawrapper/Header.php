<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper;

class Header {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function addVars(array &$page, $active = null, $page_css = null) {
        $app    = $this->app;
        $config = $app->getConfig();

        // determine active page

        if (!isset($active)) {
            $active = explode('/', $app->request()->getResourceUri());
            $active = $active[1];
        }

        // set default values

        if (!isset($config['prevent_guest_charts'])) {
            $config['prevent_guest_charts'] = false;
        }

        if (!isset($config['prevent_guest_access'])) {
            $config['prevent_guest_access'] = false;
        }

        // add "create chart" nav item

        $user      = Session::getUser();
        $headlinks = array();

        if ($user->isLoggedIn() || empty($config['prevent_guest_charts'])) {
            $headlinks[] = array(
                'url'   => '/chart/create',
                'id'    => 'chart',
                'title' => __('New Chart'),
                'icon'  => 'fa fa-plus'
            );
        }

        $this->navHook($headlinks, 'create');

        // add other configured navigation items

        if (isset($config['navigation'])) {
            foreach ($config['navigation'] as $item) {
                $headlinks[] = array(
                    'url'   => str_replace('%lang%', substr(Session::getLanguage(), 0, 2), $item['url']),
                    'id'    => $item['id'],
                    'title' => __($item['title']),
                    'icon'  => empty($item['icon']) ? null : $item['icon']
                );
            }
        }

        $this->navHook($headlinks, 'custom_nav');

        // language dropdown

        if (!empty($config['languages'])) {
            $langDropdown = array(
                'url'      => '',
                'id'       => 'lang',
                'dropdown' => array(),
                'title'    => strtoupper(substr(Session::getLanguage(), 0, 2)),
                'icon'     => false,
                'tooltip'  => __('Switch language')
            );

            foreach ($config['languages'] as $lang) {
                $langDropdown['dropdown'][] = array(
                    'url'   => '#lang-'.$lang['id'],
                    'title' => $lang['title']
                );
            }

            if (count($langDropdown['dropdown']) > 1) {
                $headlinks[] = $langDropdown;
            }
        }

        $this->navHook($headlinks, 'languages');

        // add user info

        if ($user->isLoggedIn()) {
            $headlinks[] = 'divider';

            $username = $user->guessName();

            if ($username == $user->getEmail()) {
                $username = strlen($username) > 18 ? substr($username, 0, 9).'…'.substr($username, strlen($username)-9) : $username;
            }
            elseif (strlen($username) > 18) {
                $username = substr($username, 0, 16).'…';
            }

            $headlinks[] = array(
                'url'   => '/account/',
                'id'    => 'account',
                'title' => '<img style="height:22px;position:relative;top:-2px;border-radius:7px;margin-right:7px" src="//www.gravatar.com/avatar/'.md5(strtolower(trim($user->getEmail()))).'?s=44&amp;d=mm" /><b>'.htmlspecialchars($username, ENT_QUOTES, 'UTF-8').'</b>'
            );

            if ($user->hasCharts()) {
                // mycharts
                $mycharts = array(
                    'url'        => '/mycharts/',
                    'id'         => 'mycharts',
                    'title'      => __('My Charts'),
                    //'justicon' => true,
                    'icon'       => 'fa fa-bar-chart-o',
                    'dropdown'   => array()
                );

                foreach ($user->getRecentCharts(9) as $chart) {
                    $mycharts['dropdown'][] = array(
                        'url'   => '/chart/'.$chart->getId().'/visualize#tell-the-story',
                        'title' => sprintf(
                            '<img width="30" src="%s" class="icon" /> <span>%s</span>',
                            $chart->hasPreview() ? $chart->thumbUrl(true) : '',
                            strip_tags($chart->getTitle())
                        )
                    );
                }

                $mycharts['dropdown'][] = 'divider';
                $mycharts['dropdown'][] = array('url' => '/mycharts/', 'title' => __('All charts'));

                $headlinks[] = $mycharts;
            }

            $this->navHook($headlinks, 'mycharts');
            $this->navHook($headlinks, 'settings');
        }

        // .. or add the login link

        else {
            $headlinks[] = array(
                'url'   => '#login',
                'id'    => 'login',
                'title' => $config['prevent_guest_access'] ? __('Login') : __('Login / Sign Up'),
                'icon'  => 'fa fa-sign-in'
            );
        }

        // add the logout link

        if ($user->isLoggedIn()) {
            $headlinks[] = array(
                'url'      => '#logout',
                'id'       => 'signout',
                'icon'     => 'fa fa-sign-out',
                'justicon' => true,
                'tooltip'  => __('Sign out')
            );
        }

        $this->navHook($headlinks, 'user');

        // add the admin link

        if ($user->isLoggedIn() && $user->isAdmin() && Hooks::hookRegistered(Hooks::GET_ADMIN_PAGES)) {
            $headlinks[] = 'divider';
            $headlinks[] = array(
                'url'      => '/admin',
                'id'       => 'admin',
                'icon'     => 'fa fa-gears',
                'justicon' => true,
                'tooltip'  => __('Admin')
            );
        }

        $this->navHook($headlinks, 'admin');

        // setup a custom logo

        if (Hooks::hookRegistered(Hooks::CUSTOM_LOGO)) {
            $logos = Hooks::execute(Hooks::CUSTOM_LOGO);
            $page['custom_logo'] = $logos[0];
        }

        // mark the currently active headlink as active

        foreach ($headlinks as $i => $link) {
            if ($link == 'divider') continue;
            $headlinks[$i]['active'] = ($headlinks[$i]['id'] == $active);
        }

        // setup the holy master variables for our templates

        $page['headlinks']             = $headlinks;
        $page['user']                  = Session::getUser();
        $page['language']              = substr(Session::getLanguage(), 0, 2);
        $page['locale']                = Session::getLanguage();
        $page['DW_DOMAIN']             = $config['domain'];
        $page['DW_VERSION']            = DATAWRAPPER_VERSION;
        $page['ASSET_DOMAIN']          = $config['asset_domain'];
        $page['DW_CHART_CACHE_DOMAIN'] = $config['chart_domain'];
        $page['SUPPORT_EMAIL']         = $config['email']['support'];
        $page['config']                = $config;
        $page['page_css']              = $page_css;
        $page['invert_navbar']         = isset($config['invert_header']) && $config['invert_header'] || substr($config['domain'], -4) == '.pro';
        $page['noSignup']              = $config['prevent_guest_access'];
        $page['footer']                = Hooks::execute(Hooks::GET_FOOTER);

        // determine additional plugin assets based on the current URI

        $uri           = $app->request()->getResourceUri();
        $plugin_assets = Hooks::execute(Hooks::GET_PLUGIN_ASSETS, $uri);

        if (!empty($plugin_assets)) {
            $plugin_js_files  = array();
            $plugin_css_files = array();

            foreach ($plugin_assets as $assets) {
                if (!is_array($assets)) {
                    $assets = array($assets);
                }

                foreach ($assets as $asset) {
                    $file   = $asset[0];
                    $plugin = $asset[1];

                    if (substr($file, -3) == '.js')  $plugin_js_files[]  = $file.'?v='.$plugin->getVersion();
                    if (substr($file, -4) == '.css') $plugin_css_files[] = $file.'?v='.$plugin->getVersion();
                }
            }

            $page['plugin_js']  = $plugin_js_files;
            $page['plugin_css'] = $plugin_css_files;
        }

        // add piwik stuff

        if (isset($config['piwik'])) {
            $page['PIWIK_URL']    = $config['piwik']['url'];
            $page['PIWIK_IDSITE'] = $config['piwik']['idSite'];

            if (isset($config['piwik']['idSiteNoCharts'])) {
                $page['PIWIK_IDSITE_NO_CHARTS'] = $config['piwik']['idSiteNoCharts'];
            }
        }

        // show current branch when running in debug mode

        if ($config['debug'] && file_exists(ROOT_PATH.'.git')) {
            // parse git branch
            $head   = file_get_contents(ROOT_PATH.'.git/HEAD');
            $parts  = explode('/', $head);
            $branch = trim(end($parts));

            $output = array();
            exec('git rev-parse HEAD', $output);

            $commit = $output[0];
            $page['BRANCH'] = ' (<a href="https://github.com/datawrapper/datawrapper/tree/'.$commit.'">'.$branch.'</a>)';
        }
    }

    protected function navHook(array &$headlinks, $part) {
        $links = Hooks::execute('header_nav_'.$part);

        if (!empty($links)) {
            foreach ($links as $link) {
                $headlinks[] = $link;
            }
        }
    }
}
