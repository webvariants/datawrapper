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

/**
 * This singleton handle hooks registration for plugins
 *
 * Datawrapper\Hooks::register('my_hook', 'hookFunction')
 * Datawrapper\Hooks::register('my_hook', array($this, 'hookFunction'))
 *
 * To execute a hook function:
 * Datawrapper\Hooks::execute('my_hook', $parameter, ...)
 */
class Hooks {
    private static $instance;

    public $hooks = array();

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Register a plugin hook
     *
     * @param string $hookName    the name of hook to register (see Core::Hooks)
     * @param mixed  $pluginFunc  the plugin function that will be called on hook execution (see DatawrapperPlugins::executeHook)
     */
    public static function register($hookName, $pluginFunc) {
        $me = static::getInstance();
        if (!isset($me->hooks[$hookName])) {
            $me->hooks[$hookName] = array();
        }
        $me->hooks[$hookName][] = $pluginFunc;
    }

    /**
     * Execute a core hook - will call every plugin function registred for a hook
     *
     * @param string $hookName  the name of hook to register (see Core::Hooks)
     * @param mixed  $params    parameters that will be passed to plugin functions
     */
    public static function execute($hookName) {
        $me = static::getInstance();
        if (!isset($me->hooks[$hookName])) {
            return false;
        }
        $results = array();
        foreach ($me->hooks[$hookName] as $key => $func) {
            $results[] = call_user_func_array($func, array_slice(func_get_args(), 1));
        }
        return $results;
    }

    /**
     * Checks whether a hook has been registered or if executing
     * that hook would lead to no action.
     *
     * @param string $hookName  the name of hook to register (see Core::Hooks)
     */
    public static function hookRegistered($hookName) {
        $me = static::getInstance();
        return isset($me->hooks[$hookName]);
    }

    // print something below a charts HTML body
    const CHART_AFTER_BODY = 'chart_after_body';

    // print something in <head> section of a chart
    const CHART_HTML_HEAD = 'chart_html_head';

    // print something below the Datawrapper HTML body
    const CORE_AFTER_BODY = 'core_after_body';

    // publishes a set of files to some CDN (args: files)
    const PUBLISH_FILES = 'publish_files';

    // unpublishes (removes) a set of files from some CDN (args: files)
    const UNPUBLISH_FILES = 'unpublish_files';

    // returns the URL of a published chart (args: chart)
    const GET_PUBLISHED_URL = 'get_pulished_url';

    // returns a key specifically for the storage (eg the s3 bucket)
    const GET_PUBLISH_STORAGE_KEY = 'get_publish_storage_key';

    // Send an email (args: to, subject, body, headers)
    const SEND_EMAIL = 'send_email';

    // Runs once a day via cronjob
    const CRON_DAILY = 'cron_daily';

    // Runs every hour via cronjob
    const CRON_HOURLY = 'cron_hourly';

    // Runs every half hour via cronjob
    const CRON_HALF_HOURLY = 'cron_half_hourly';

    // Runs every 15 minutes via cronjob
    const CRON_QUARTER_HOURLY = 'cron_quarter_hourly';

    // Runs every 15 minutes via cronjob
    const CRON_FIVE_MINUTELY = 'cron_five_minutely';

    // Runs every minute via cronjob
    const CRON_MINUTELY = 'cron_minutely';

    // Executed after a chart has been published (args: chart, user)
    const POST_CHART_PUBLISH = 'post_chart_publish';

    // returns a list of actions to be displayed in publish step
    const GET_CHART_ACTIONS = 'get_chart_actions';

    // render something beneath the chart actions
    const PUBLISH_AFTER_CHART_ACTIONS = 'publish_after_chart_actions';

    // render something after sidebar on mycharts page
    const MYCHARTS_AFTER_SIDEBAR = 'mycharts_after_sidebar';

    // a hook for providing new api actions
    const PROVIDE_API = 'provide_api';

    // a hook for providing new api actions
    const GET_PLUGIN_ASSETS = 'get_plugin_assets';

    // a hook for providing new demo datasets
    const GET_DEMO_DATASETS = 'get_demo_assets';

    // a hook for providing visualization options
    const VIS_OPTION_CONTROLS = 'vis_option_controls';

    // overwrite the default footer
    const GET_FOOTER = 'get_footer';

    // allow plugins to add new admin pages
    const GET_ADMIN_PAGES = 'get_admin_pages';

    // allow plugins to add new admin pages
    const ALTERNATIVE_SIGNIN = 'alternative_signin';

    // allow adding links after 'create new chart'
    const HEADER_NAV = 'header_nav_';

    // add a logo to header navbar
    const CUSTOM_LOGO = 'custom_logo';

    // a new user as bean created
    const USER_SIGNUP = 'user_signup';

    const USER_ORGANIZATION_ADD = 'user_organization_add';
    const USER_ORGANIZATION_REMOVE = 'user_organization_remove';

	const VISUALIZE_AFTER_THEME_SELECTOR = 'visualize_after_theme_selector';

    // extend the settings form
    const USER_SETTINGS = 'user_settings';

    // extend the settings navigation
    const USER_SETTINGS_NAV = 'user_settings_nav';

    // extend account pages
    const GET_ACCOUNT_PAGES = 'get_account_pages';
}

