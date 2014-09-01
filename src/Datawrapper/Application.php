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

use Datawrapper\Publishing;
use HTMLPurifier;
use HTMLPurifier_Config;
use Slim\Slim;

class Application extends Slim {
    public function __construct(array $userSettings = array()) {
        parent::__construct($userSettings);

        $app = $this;

        $this->container->singleton('dw_htmlpurifier', function () {
            // Twig Extension to clean HTML from malicious code
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'a[href],p,b,div,span,strong,u,i,em,q,blockquote,*[style],br,small');
            $config->set('Cache.SerializerPath', ROOT_PATH.'/tmp/');

            return new HTMLPurifier($config);
        });

        $this->container->singleton('dw_publisher', function () use ($app) {
            // determine best chart status holder
            if (isset($_GLOBALS['dw-config']['memcache'])) {
                $statusHolder = new Publishing\MemcacheStatus($_GLOBALS['dw-config']['memcache']);
            }
            else {
                $statusHolder = new Publishing\FilesystemStatus();
            }

            return new Publishing\Publisher($statusHolder, $app->dw_chart_view);
        });

        $this->container->singleton('dw_chart_view', function () use ($app) {
            $config = $app->getConfig();

            return new Publishing\ChartView(
                $config['domain'],
                $config['asset_domain'],
                $config['chart_domain'],
                $config['debug']
            );
        });
    }

    public function getPlugin($id) {
        return PluginManager::getInstance('datawrapper-home');
    }

    public function getI18N() {
        return $GLOBALS['__l10n'];
    }

    public function getConfig($key = null) {
        $config = $GLOBALS['dw_config'];

        if ($key !== null) {
            return array_key_exists($key, $config) ? $config[$key] : null;
        }

        return $config;
    }
}
