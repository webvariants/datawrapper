<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\ORM\UserQuery;

/*
 * This scripts secures all passwords in the database with the
 * secure_auth_key that was introduced in 1.3.2.
 *
 * Please run this script only once(!) when migrating to 1.3.2 and
 * please BACK UP YOUR DATABASE first, in case something goes wrong.
 *
 * OTHERWISE your users won't be able to login anymore.
 */

define('ROOT_PATH', '../../');
define('NO_SLIM', 1);

require_once ROOT_PATH . 'lib/bootstrap.php';

if (empty($dw_config['secure_auth_key'])) {
    die("You need to specify a secure auth key in config.yaml");
}

foreach (UserQuery::create()->find() as $user) {
    $user->setPwd(secure_password($user->getPwd()));
    $user->save();
}

print "ok.\n";
