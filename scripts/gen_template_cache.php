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

define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
define('NO_SLIM', 1);

require_once ROOT_PATH.'lib/bootstrap.php';

class CustomTwigEnvironment extends Twig_Environment {
    public function getCacheFilename($name) {
        $name  = str_replace(DIRECTORY_SEPARATOR, '/', $name);
        $parts = explode('/', $name);

        // basically flatten the relative file path and make sure that plugin template cache files
        // are located in plugins/<plugin>/..., so when parsing them with xgettext, they can be
        // properly filtered

        if ($parts[0] === 'plugins') {
            $path = 'plugins/'.$parts[1].'/'.implode('__', array_slice($parts, 2));
        }
        else {
            $path = implode('__', $parts);
        }

        return $this->getCache().'/'.$path.'.php';
    }
}

$tplDir = ROOT_PATH.'templates';
$loader = new Twig_Loader_Filesystem($tplDir);
$twig   = new CustomTwigEnvironment($loader);

dwInitTwigEnvironment($twig);
date_default_timezone_set('Europe/Berlin');

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir, RecursiveDirectoryIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
    if (substr($file, -5) == '.twig') {
        $tplPath = str_replace($tplDir.DIRECTORY_SEPARATOR, '', $file);
        $tplPath = str_replace(DIRECTORY_SEPARATOR, '/', $tplPath);

        print $tplPath;

        // force compilation
        $twig->loadTemplate($tplPath);

        print PHP_EOL;
    }
}
