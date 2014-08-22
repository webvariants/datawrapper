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
 * Generic hook script
 */

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('NO_SLIM', 1);
define('NO_SESSION', 1);

require_once ROOT_PATH . 'src/bootstrap.php';
date_default_timezone_set('Europe/Berlin');

$hook = $argv[1];

if (!empty($hook)) {
    Datawrapper\Hooks::execute($argv[1]);
}

