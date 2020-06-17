<?php

namespace Core;

use mysqli;
use mysqli_stmt;
use mysqli_result;

use Exception;

class Db extends mysqli {
    const MYSQLI_BIND_TYPE_DOUBLE = 'd';
    const MYSQLI_BIND_TYPE_STRING = 's';

    const MYSQLI_BIND_TYPE_BOOL = 'i';
    const MYSQLI_BIND_TYPE_INTEGER = 'i';

    protected static array $INSTANCES = [];

    private static $_host;
    private static $_dbname;
    private static $_username;
    private static $_passwd;
    private static $_port;
    private static $_socket;

    private function __construct() {     
        parent::__construct(self::$_host, self::$_username, self::$_passwd, self::$_dbname, self::$_port, self::$_socket);

        if( mysqli_connect_errno() ) {
            throw new exception(mysqli_connect_error(), mysqli_connect_errno()); 
        }
    }

    /**
     * Zwraca instancje bazy danych
     * 
     * @param string $section
     * @return Db
     */
    public static function getInstance($section = 'database') {
        if( !isset(self::$INSTANCES[$section]) ) {
            self::setOptions($section); 
            self::$INSTANCES[$section] = new self(); 
        }
        return self::$INSTANCES[$section];
    }

    private static function setOptions($section) {
        $CONFIG = parse_ini_file('config' . DS . 'db.ini', true);
        $CONFIG = $CONFIG[$section];

        self::$_host = isset($CONFIG['host']) ? $CONFIG['host'] : null;
        self::$_dbname = isset($CONFIG['db']) ? $CONFIG['db'] : null;
        self::$_username = isset($CONFIG['user']) ? $CONFIG['user'] : null;
        self::$_passwd = isset($CONFIG['password']) ? $CONFIG['password'] : null;
        self::$_port = isset($CONFIG['port']) ? $CONFIG['port'] : null;
        self::$_socket = isset($CONFIG['socket']) ? $CONFIG['socket'] : null;
    }

    public function prepare($query) {
        $stmt = new mysqli_stmt($this, $query);
        return $stmt;
    }

    /**
     * Dodaje nowy rekord do DB
     * 
     * @param string $query
     * @param array $BIND
     * 
     * @return int|bool
     */
    public function insert($query, $BIND = []) {
        $stmt = $this->_prepareQuery($query, $BIND);
        if ($stmt->execute()) {
            if (empty($stmt->insert_id))
                return true;
                
            return (int)$stmt->insert_id;
        }

        return false;
    }



    /**
     * Aktualizuje dane w DB
     * 
     * @param string $query
     * @param array $BIND
     * 
     * @return int|bool
     */
    public function update($query, $BIND = []) {
        $stmt = $this->_prepareQuery($query, $BIND);
        if ($stmt->execute())
            return (int)$stmt->affected_rows;

        return false;
    }

    /**
     * Zwraca pierwsza wartosc z pierwszego rekordu
     * 
     * @param string $query
     * @param array $BIND
     * 
     * @return string|bool
     */
    public function fetchOne($query, $BIND = []) {
        $stmt = $this->_prepareQuery($query, $BIND);
        if($stmt->execute()) {
            $result = $stmt->get_result();

            if (!$result) {
                return false;
            }

            $ROW = $result->fetch_array(MYSQLI_NUM);
            return (string)$ROW[0];
        }

        return false;
    }

    /**
     * Zwraca pierwszpy rekord
     * 
     * @param string $query
     * @param array $BIND
     * @return array|bool
     */
    public function fetchRow($query, $BIND = []) {
        $stmt = $this->_prepareQuery($query, $BIND);
        if($stmt->execute()) {
            $result = $stmt->get_result();

            if (!$result) {
                return false;
            }

            return (array)$result->fetch_array(MYSQLI_ASSOC);
        }

        return false;
    }

    /**
     * Zwraca wszystkie znalezione rekordy
     * 
     * @param string $query
     * @param array $BIND
     * @return array|bool
     */
    public function fetchAll($query, $BIND = []) {
        $stmt = $this->_prepareQuery($query, $BIND);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            
            if (!$result) {
                return false;
            }

            return (array)$result->fetch_all(MYSQLI_ASSOC);
        }

        return false;
    }

    /**
     * Przygotowuje zapytanie i zajmuje sie wiazaniem wszystkich zmiennych do zapytania
     * 
     * @param string $query
     * @param array $BIND
     * @return mysqli_stmt
     */
    private function _prepareQuery($query, $BIND = []) {
        $stmt = $this->prepare($query);
        if(empty($BIND)) {
            return $stmt;
        }

        $PARAMS = [0 => ''];
        foreach ($BIND as $PARAM) {
            switch($PARAM['mysqliBindType']) {
                case self::MYSQLI_BIND_TYPE_DOUBLE:
                    $PARAMS[0] .= self::MYSQLI_BIND_TYPE_DOUBLE;
                    break;
                case self::MYSQLI_BIND_TYPE_INTEGER:
                    $PARAMS[0] .= self::MYSQLI_BIND_TYPE_INTEGER;
                    break;
                case self::MYSQLI_BIND_TYPE_BOOL:
                    $PARAMS[0] .= self::MYSQLI_BIND_TYPE_BOOL;
                    break;
                case self::MYSQLI_BIND_TYPE_STRING:
                default:
                    $PARAMS[0] .= self::MYSQLI_BIND_TYPE_STRING;
                    break;
            }

            $PARAMS[] = &$PARAM['value'];
        }

        call_user_func_array([$stmt, 'bind_param'], $PARAMS);
        return $stmt;
    }
}