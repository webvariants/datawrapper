<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\SessionHandler;

/**
 * Custom save handler for Datawrapper sessions
 */
class DatabaseHandler {
    protected $db;

    public function __construct(\PDO $conn) {
        $this->db = $conn;
    }

    public static function initialize() {
        $conf    = \Propel::getConfiguration(\PropelConfiguration::TYPE_ARRAY);
        $dbconf  = $conf['datasources']['datawrapper']['connection'];
        $pdo     = new \PDO($dbconf['dsn'], $dbconf['user'], $dbconf['password']);
        $handler = new static($pdo);

        session_set_save_handler(
            array($handler, 'open'),
            array($handler, 'close'),
            array($handler, 'read'),
            array($handler, 'write'),
            array($handler, 'destroy'),
            array($handler, 'gc')
        );

        return $handler;
    }

    public function open($sess_path, $sess_name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sess_id) {
        $result = $this->db->query("SELECT session_data FROM session WHERE session_id = '$sess_id'");
        if ($result->rowCount() === 0) {
            $this->db->exec("INSERT INTO session (session_id, date_created, last_updated, session_data) VALUES ('$sess_id', NOW(), NOW(), '')");
            return '';
        }

        $res = $result->fetch(\PDO::FETCH_ASSOC);
        $this->db->exec("UPDATE session SET last_updated = NOW() WHERE session_id = '$sess_id'");

        return $res['session_data'];
    }

    public function write($sess_id, $data) {
        $this->db->exec("UPDATE session SET session_data = '$data', last_updated = NOW() WHERE session_id = '$sess_id'");
        return true;
    }

    public function destroy($sess_id) {
        $this->db->exec("DELETE FROM session WHERE session_id = '$sess_id'");
        return true;
    }

    public function gc($sess_maxlifetime) {
        $this->db->exec("DELETE FROM session WHERE session_data = \"slim.flash|a:0:{}\" AND last_updated < '".date('c', time()-86400*30)."'");
        return true;
    }
}
