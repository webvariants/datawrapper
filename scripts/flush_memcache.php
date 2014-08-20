<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Session;

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('NO_SLIM', 1);
define('NO_SESSION', 1);

require ROOT_PATH . 'lib/bootstrap.php';

if (isset($dw_config['memcache'])) {
    $memcache->flush();
    print "flushed memcache!\n";
} else {
    print "memcache is not configured.\n";
}

Session::setLanguage("de_DE");
print Session::getLanguage()."\n";
print __("This little tool reduces the time needed to create a correct chart and embed it into any website from hours to seconds. It makes charting easy, and helps you avoiding common pitfalls.");
