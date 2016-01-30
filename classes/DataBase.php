<?php
defined('GOOGLEIMAGES') or die('Access denied');

class DataBase {
    
    private $_connection;
    private static $_DBObj;
    
    public static function getDBObj() {
        if(!self::$_DBObj) {
            self::$_DBObj = new self();
        }
        return self::$_DBObj;
    }
    
    public function __construct() {
        $this->_connection = new mysqli(HOST, USER, PASS, DB);
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $this->_connection->set_charset('utf8');
    }
    
    private function __clone() {}
    
    public function getConnection() {
        return $this->_connection;
    }
}