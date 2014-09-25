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
use Datawrapper\Visualization;
use Datawrapper\ORM\Chart;
use Datawrapper\ORM\User;

class Publisher {
    protected $status;
    protected $chartView;

    public function __construct($statusHolder, ChartView $chartView) {
        $this->status    = $statusHolder;
        $this->chartView = $chartView;
    }

    public function publishChart(User $user, Chart $chart, $fromCli = false) {
        $files = array();

        if (!$fromCli) $this->setStatus($chart, 0.01);
        else print "Publishing chart ".$chart->getID().".\n";

        $files = array_merge($files, $this->publishHtml($user, $chart));
        $files = array_merge($files, $this->publishCSS($user, $chart));
        $files = array_merge($files, $this->publishData($user, $chart));
        $files = array_merge($files, $this->publishJS($user, $chart));

        if (!$fromCli) $this->setStatus($chart, 0.3);
        else print "Files stored to static folder (html, css, data, js)\n";

        $totalSize = 0;  // total file size
        foreach ($files as $i => $file) {
            $totalSize += filesize($file[0]);
        }

        $done = 0;
        foreach ($files as $file) {
            $this->pushToCDN(array($file), $chart);

            $done += filesize($file[0]);
            $this->setStatus($chart, 0.3 + ($done / $totalSize) * 0.7);
        }

        if (!$fromCli) {
            $this->setStatus($chart, 1);
            $this->clearStatus($chart);
        }
        else {
            print "Files pushed to CDN.\n";
        }

        $chart->redirectPreviousVersions();

        Hooks::execute(
            Hooks::POST_CHART_PUBLISH,
            $chart, $user
        );
    }

    public static function getStaticPath(Chart $chart) {
        $path = ROOT_PATH.'charts/static/'.$chart->getID();

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

    public function getStatus(Chart $chart) {
        return $this->status->get($chart);
    }

    protected function setStatus(Chart $chart, $status) {
        $this->status->set($chart, round($status * 100));
    }

    protected function clearStatus($chart) {
        $this->status->clear($chart);
    }

    protected function publishHtml(User $user, Chart $chart) {
        $cdn_files   = array();
        $static_path = self::getStaticPath($chart);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $url      = $protocol.'://'.$GLOBALS['dw_config']['domain'].'/chart/'.$chart->getID().'/preview?minify=1';

        $this->download($url,            $static_path.'/index.html');
        $this->download($url.'&plain=1', $static_path.'/plain.html');
        $this->download($url.'&fs=1',    $static_path.'/fs.html');

        $chart->setPublishedAt(time() + 5);
        $chart->setLastEditStep(5);
        $chart->save();

        $cdn_files[] = array($static_path.'/index.html', $chart->getCDNPath().'index.html', 'text/html');
        $cdn_files[] = array($static_path.'/plain.html', $chart->getCDNPath().'plain.html', 'text/html');
        $cdn_files[] = array($static_path.'/fs.html',    $chart->getCDNPath().'fs.html',    'text/html');

        // copy empty image as placeholder for nojs.png
        file_put_contents($static_path.'/nojs.png', file_get_contents(ROOT_PATH.'www/static/img/nojs.png'));

        return $cdn_files;
    }

    protected function publishJS(User $user, Chart $chart) {
        $cdn_files   = array();
        $static_path = ROOT_PATH.'charts/static/lib/';
        $data        = $this->chartView->getData($chart, $user, false, true);
        $now         = date('c');

        ////////////////////////////////////////////////////////////////////////
        // generate visualization script

        $vis    = $data['visualization'];
        $vis_js = $data['vis_js'];

        if (!file_exists($static_path.$vis_js[0])) {
            // add comment
            $vis_js[1] = sprintf("/*\n * datawrapper / vis / {%s} v{%s}\n * generated on %s\n */\n%s", $vis['id'], $vis['version'], $now, $vis_js[1]);

            file_put_contents(ROOT_PATH.'www'.$static_path.$vis_js[0], $vis_js[1]);

            $cdn_files[] = array(
                $static_path.$vis_js[0],
                'lib/'.$vis_js[0],
                'application/javascript'
            );
        }

        ////////////////////////////////////////////////////////////////////////
        // generate theme script

        $theme    = $data['theme'];
        $theme_js = $data['theme_js'];

        if (!file_exists($static_path.$theme_js[0])) {
            // add comment
            $theme_js[1] = sprintf("/*\n * datawrapper / theme / {%s} v{%s}\n * generated on %s\n */\n%s", $theme['id'], $theme['version'], $now, $theme_js[1]);

            file_put_contents(ROOT_PATH.'www'.$static_path.$theme_js[0], $theme_js[1]);
        }

        $cdn_files[] = array(
            $static_path.$theme_js[0],
            'lib/'.$theme_js[0],
            'application/javascript'
        );

        ////////////////////////////////////////////////////////////////////////
        // generate chart script

        $chart_js = $data['chart_js'];

        if (!file_exists($static_path.$chart_js[0])) {
            // add comment
            $chart_js[1] = sprintf("/*\n * datawrapper / chart\n * generated on %s\n */\n%s", $now, $chart_js[1]);

            file_put_contents(ROOT_PATH.'www'.$static_path.$chart_js[0], $chart_js[1]);
        }

        $cdn_files[] = array(
            $static_path.$chart_js[0],
            'lib/'.$chart_js[0],
            'application/javascript'
        );

        return $cdn_files;
    }

    protected function publishCSS(User $user, Chart $chart) {
        $cdn_files   = array();
        $static_path = self::getStaticPath($chart);
        $data        = $this->chartView->getData($chart, $user, false, true);
        $all         = '';

        foreach ($data['stylesheets'] as $css) {
            $all .= file_get_contents(ROOT_PATH.'www'.$css)."\n\n";
        }

        // move @imports to top of file

        $imports = array();
        $body    = '';
        $lines   = explode("\n", $all);

        foreach($lines as $line) {
            if (substr($line, 0, 7) == '@import') $imports[] = $line;
            else $body .= $line."\n";
        }

        $all = implode("\n", $imports)."\n\n".$body;
        file_put_contents($static_path.'/'.$chart->getID().'.all.css', $all);

        $cdn_files[] = array(
            $static_path.'/'.$chart->getID().'.all.css',
            $chart->getCDNPath().$chart->getID().'.all.css',
            'text/css'
        );

        // copy themes assets

        $theme = $data['theme'];
        if (isset($theme['assets'])) {
            foreach ($theme['assets'] as $asset) {
                $asset_src = ROOT_PATH.'www/'.$theme['__static_path'].'/'.$asset;
                $asset_dst = $static_path.'/'.$asset;

                if (file_exists($asset_src)) {
                    copy($asset_src, $asset_dst);
                    $cdn_files[] = array($asset_src, $chart->getCDNPath().$asset);
                }
            }
        }

        // copy visualization assets

        $vis    = $data['visualization'];
        $assets = Visualization::assets($vis['id'], $chart);

        foreach ($assets as $asset) {
            $asset_src = ROOT_PATH.'www/static/'.$asset;
            $asset_dst = $static_path.'/assets/'.$asset;

            mkdir($asset_dst, 0777, true);
            copy($asset_src, $asset_dst);

            $cdn_files[] = array($asset_src, $chart->getCDNPath().'assets/'.$asset);
        }

        return $cdn_files;
    }

    protected function publishData(User $user, Chart $chart) {
        $cdn_files   = array();
        $static_path = self::getStaticPath($chart);

        file_put_contents($static_path.'/data.csv', $chart->loadData());
        $cdn_files[] = array($static_path.'/data.csv', $chart->getCDNPath().'data.csv', 'text/plain');

        return $cdn_files;
    }

    protected function pushToCDN($cdn_files, Chart $chart) {
        Hooks::execute(Hooks::PUBLISH_FILES, $cdn_files);
    }

    protected function download($url, $outf) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $fp = fopen($outf, 'w');

            $strCookie = 'DW-SESSION='.$_COOKIE['DW-SESSION'].'; path=/';
            session_write_close();

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0 );
            curl_setopt($ch, CURLOPT_COOKIE, $strCookie);

            if (isset($GLOBALS['dw_config']['http_auth'])) {
                curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['dw_config']['http_auth']);
            }

            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        else {
            $cfg = array(
                'http' => array(
                    'header' => 'Connection: close\r\n',
                    'method' => 'GET'
                )
            );

            if (isset($GLOBALS['dw_config']['http_auth'])) {
                $cfg['http']['header'] .= "Authorization: Basic ".base64_encode($GLOBALS['dw_config']['http_auth']).'\r\n';
            }

            $context = stream_context_create($cfg);
            $html    = file_get_contents($url, false, $context);

            file_put_contents($outf, $html);
        }
    }
}

