<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/*
 * this scripts re-generates the PHP cache for all Twig templates
 * this step is needed because xgettext cannot parse Twig templates
 * (but PHP scripts)
 */

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('NO_SLIM', 1);

require_once ROOT_PATH . 'lib/bootstrap.php';

date_default_timezone_set('Europe/Berlin');

// taken from http://twig.sensiolabs.org/doc/extensions/i18n.html#extracting-template-strings

Twig_Autoloader::register();

$tplDir = ROOT_PATH . 'templates';
$tmpDir = ROOT_PATH . 'scripts/tmpl_cache/';
$tmpDirPlugins = ROOT_PATH . 'scripts/tmpl_cache/plugins/';
$loader = new Twig_Loader_Filesystem($tplDir);

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment($loader);
dwInitTwigEnvironment($twig);

if (!file_exists($tmpDirPlugins)) {
    mkdir($tmpDirPlugins);
}

// iterate over all your templates
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir, RecursiveDirectoryIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
    if (substr($file, -5) == ".twig") {
        // force compilation
        $tmplPath = str_replace($tplDir.'/', '', $file);
        $twig->loadTemplate($tmplPath);
        $cacheFile = $twig->getCacheFilename($tmplPath);
        $compiled = file_get_contents($cacheFile);
        $outPath = $tmpDir . str_replace("/", "__", $tmplPath).".php";
        if (substr($tmplPath, 0, 8) == 'plugins/') {
            $outPath = $tmpDirPlugins . str_replace("/", "__", substr($tmplPath, 8)).".php";
        }
        file_put_contents($outPath, $compiled);
        unlink($cacheFile);
    }
}

// clean up
// exec('rm -Rf ??');
