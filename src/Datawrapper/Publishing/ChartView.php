<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\Publishing;

use Datawrapper\Hooks;
use Datawrapper\ORM\Chart;
use Datawrapper\ORM\User;
use Datawrapper\Session;
use Datawrapper\Theme;
use Datawrapper\Visualization;

class ChartView {
    protected $domain;
    protected $chartDomain;
    protected $assetDomain;
    protected $debug;

    public function __construct($domain, $chartDomain, $assetDomain, $debug = false) {
        $this->domain      = $domain;
        $this->chartDomain = $chartDomain;
        $this->assetDomain = $assetDomain;
        $this->debug       = !!$debug;
    }

    public function getData(Chart $chart, User $user, $published = false, $debug = false) {
        $theme_css = array();
        $theme_js  = array();
        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        $locale = Session::getLanguage();
        if ($chart->getLanguage() != '') {
            $locale = $chart->getLanguage();
        }

        $next_theme_id = $chart->getTheme();

        while (!empty($next_theme_id)) {
            $theme = Theme::get($next_theme_id);
            $theme_js[] = $theme['__static_path'].$next_theme_id.'.js';
            if ($theme['hasStyles']) {
                $theme_css[] =  $theme['__static_path'].$next_theme_id.'.css';
            }
            $next_theme_id = $theme['extends'];
        }

        $abs   = $protocol.'://'.$this->domain;
        $debug = $this->debug || $debug;

        if ($published && !$debug && !empty($this->assetDomain)) {
            $base_js = array(
                '//'.$this->assetDomain.'/globalize.min.js',
                '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js',
                '//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js'
            );

            if (substr($locale, 0, 2) != 'en') {
                $base_js[] = '//'.$this->assetDomain.'/cultures/globalize.culture.'.str_replace('_', '-', $locale).'.js';
            }
        }
        else {
            // use local assets
            $base_js = array(
                $abs.'/static/vendor/globalize/globalize.min.js',
                $abs.'/static/vendor/underscore/underscore-1.5.2.min.js',
                $abs.'/static/vendor/jquery/jquery-1.10.2'.($debug ? '' : '.min').'.js'
            );

            if (substr($locale, 0, 2) != 'en') {
                $base_js[] = $abs.'/static/vendor/globalize/cultures/globalize.culture.'.str_replace('_', '-', $locale).'.js';
            }
        }

        $vis_js      = array();
        $vis_css     = array();
        $next_vis_id = $chart->getType();

        $vis_libs       = array();
        $vis_libs_cdn   = array();
        $vis_libs_local = array();

        $vis_locale = array();  // visualizations may define localized strings, e.g. "other"

        while (!empty($next_vis_id)) {
            $vis = Visualization::get($next_vis_id);
            $vjs = array();

            if (!empty($vis['libraries'])) {
                foreach ($vis['libraries'] as $url) {
                    if (!is_array($url)) {
                        $url = array('local' => $url, 'cdn' => false);
                    }

                    if ($url['cdn']) {
                        $vis_libs_cdn[] = $url['cdn'];
                    }

                    // at first we check if the library lives in ./lib of the vis module
                    if (file_exists(ROOT_PATH.'www/'.$vis['__static_path'].$url['local'])) {
                        $u = $vis['__static_path'].$url['local'];
                    }
                    elseif (file_exists(ROOT_PATH.'www/static/vendor/'.$url['local'])) {
                        $u = '/static/vendor/'.$url['local'];
                    }
                    else {
                        die('could not find required library '.$url['local']);
                    }

                    $vis_libs[] = $u;

                    if (!$url['cdn']) {
                        $vis_libs_local[] = $u;
                    }
                }
            }

            if (!empty($vis['locale']) && is_array($vis['locale'])) {
                foreach ($vis['locale'] as $term => $translations) {
                    if (!isset($vis_locale[$term])) {
                        $vis_locale[$term] = $translations;
                    }
                }
            }

            $vjs[] = $vis['__static_path'].$vis['id'].'.js';
            $vis_js = array_merge($vis_js, array_reverse($vjs));

            if ($vis['hasCSS']) {
                $vis_css[] = $vis['__static_path'].$vis['id'].'.css';
            }

            $next_vis_id = !empty($vis['extends']) ? $vis['extends'] : null;
        }

        $stylesheets = array_merge(
            array('/static/css/chart-base.min.css'),
            $vis_css,
            array_reverse($theme_css)
        );

        $the_vis = Visualization::get($chart->getType());
        $the_vis['locale'] = $vis_locale;
        $the_theme = Theme::get($chart->getTheme());
        $l10n__domain = $the_theme['__static_path'];

        $the_vis_js = $this->get_vis_js($the_vis, array_merge(array_reverse($vis_js), $vis_libs_local));
        $the_theme_js = $this->get_theme_js($the_theme, array_reverse($theme_js));
        $the_chart_js = $this->get_chart_js();

        if ($published) {
            $scripts = array_merge(
                $base_js,
                $vis_libs_cdn,
                array(
                    '/lib/'.$the_vis_js[0],
                    '/lib/'.$the_theme_js[0],
                    '/lib/'.$the_chart_js[0]
                )
            );
            $stylesheets = array($chart->getID().'.all.css');
            // NOTE: replace `/static/` by `assets/` in the `__static_path` value,
            //       since vis assets are handle by Visualization
            $replace_in = $the_vis['__static_path'];
            $replace_by = 'assets/'; $replace = '/static/';

            $the_vis['__static_path'] = substr_replace(
                $replace_in,                    // subject
                $replace_by,                    // replace by
                strrpos($replace_in, $replace), // position
                strlen($replace));              // length
            $the_theme['__static_path'] = '';
        }
        else {
            $scripts = array_unique(
                array_merge(
                    $base_js,
                    array('/static/js/dw-2.0'.($debug ? '' : '.min').'.js'),
                    array_reverse($theme_js),
                    array_reverse($vis_js),
                    $vis_libs,
                    array('/static/js/dw/chart.base.js')
                )
            );
        }

        $published_urls = Hooks::execute(Hooks::GET_PUBLISHED_URL, $chart);

        if (empty($published_urls)) {
            $chart_url = $protocol.'://'.$this->chartDomain.'/'.$chart->getID().'/';
        }
        else {
            $chart_url = $published_urls[0];  // ignore urls except from the first one
        }

        $embedCode = sprintf(
            '<iframe src="%s" width="%s" height="%s" frameborder="0" allowtransparency="true" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen></iframe>',
            $chart_url, $chart->getMetadata('publish.embed-width'), $chart->getMetadata('publish.embed-height')
        );

        return array(
            'chartData'     => $chart->loadData(),
            'chart'         => $chart,
            'lang'          => strtolower(substr($locale, 0, 2)),
            'metricPrefix'  => get_metric_prefix($locale),
            'l10n__domain'  => $l10n__domain,
            'origin'        => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
            'DW_DOMAIN'     => $protocol.'://'.$this->domain.'/',
            'DW_CHART_DATA' => $protocol.'://'.$this->domain.'/chart/'.$chart->getID().'/data.csv',
            'ASSET_PATH'    => $published ? '' : $the_theme['__static_path'],
            'chartUrl'      => $chart_url,
            'embedCode'     => $embedCode,
            'chartUrlFs'    => strpos($chart_url, '.html') > 0 ? str_replace('index.html', 'fs.html', $chart_url) : $chart_url.'?fs=1',

            // used in chart.twig
            'stylesheets'   => $stylesheets,
            'scripts'       => $scripts,
            'visualization' => $the_vis,
            'theme'         => $the_theme,
            'chartLocale'   => str_replace('_', '-', $locale),

            // the following is used when publishing a chart
            'vis_js'   => $the_vis_js,
            'theme_js' => $the_theme_js,
            'chart_js' => $the_chart_js
        );
    }

    /**
     * returns an array
     *   [0] filename of the vis js class, eg, vis/column-chart-7266c4ee39b3d19f007f01be8853ac87.min.js
     *   [1] minified source code
     */
    protected function get_vis_js(array $vis, array $visJS) {
        // always prepend our base dw JS code
        array_unshift($visJS, '/static/js/dw-2.0.min.js');

        list ($hash, $code) = $this->concatFiles($visJS);
        $vis_path          = 'vis/'.$vis['id'].'-'.$hash.'.min.js';

        return array($vis_path, $code);
    }

    /**
     * returns an array
     *   [0] filename of the theme js class, eg, theme/default-7266c4ee39b3d19f007f01be8853ac87.min.js
     *   [1] minified source code
     */
    protected function get_theme_js(array $theme, array $themeJS) {
        list ($hash, $code) = $this->concatFiles($themeJS);
        $theme_path         = 'theme/'.$theme['id'].'-'.$hash.'.min.js';

        return array($theme_path, $code);
    }

    protected function get_chart_js() {
        $js  = file_get_contents(ROOT_PATH.'www/static/js/dw/chart.base.js');
        $md5 = md5($js);

        return array('chart-'.$md5.'.min.js', $js);
    }

    /**
     * merge multiple js files into a single file
     *
     * @return array  [hash, jscode]
     */
    protected function concatFiles(array $jsFiles) {
        // concat the files

        $all = '';

        foreach ($jsFiles as $js) {
            // if the file is not an absolute URI, read it and concat the files
            if (substr($js, 0, 7) != 'http://' && substr($js, 0, 8) != 'https://' && substr($js, 0, 2) != '//') {
                $all .= "\n\n\n".file_get_contents(ROOT_PATH.'www'.$js);
            }
        }

        $all = \JShrink\Minifier::minify($all);

        // create a special marker, based on the organisation this chart belongs to

        $org  = Session::getUser()->getCurrentOrganization();
        $keys = Hooks::execute(Hooks::GET_PUBLISH_STORAGE_KEY);
        $sig  = $org ? '/'.$org->getID() : '';

        if (is_array($keys)) {
            $sig .= '/'.implode('/', $keys);
        }

        $hash = md5(md5($all).$sig);

        // done

        return array($hash, $all);
    }
}
