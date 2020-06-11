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

    public static function getInstance($section) {
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

    public function query($query, $resultmode = null) {
        if( !$this->real_query($query) ) {
            throw new exception( $this->error, $this->errno );
        }

        $result = new mysqli_result($this);
        return $result;
    }

    public function prepare($query) {
        $stmt = new mysqli_stmt($this, $query);
        return $stmt;
    } 

    /**
     * Fetch first value in first column 
     * 
     * @param string $query
     * @param array $BIND
     * 
     * @return string|bool
     */
    public function fetchOne($query, $BIND = []) {
        $result = $this->_prepareQuery($query, $BIND);
        if (!$result) {
            return false;
        }

        $ROW = $result->fetch_array(MYSQLI_NUM);
        return (string)$ROW[0];
    }

    /**
     * Fetch first row
     * 
     * @param string $query
     * @param array $BIND
     * @return array|bool
     */
    public function fetchRow($query, $BIND = []) {
        $result = $this->_prepareQuery($query, $BIND);
        if (!$result) {
            return false;
        }

        return (array)$result->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * Fetch all
     * 
     * @param string $query
     * @param array $BIND
     * @return array|bool
     */
    public function fetchAll($query, $BIND = []) {
        $result = $this->_prepareQuery($query, $BIND);        
        if (!$result) {
            return false;
        }

        return (array)$result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch first row
     * 
     * @param string $query
     * @param array $BIND
     * @return mysqli_result|false
     */
    private function _prepareQuery($query, $BIND = []) {
        $stmt = $this->prepare($query);
        foreach ($BIND as $PARAM) {
            switch($PARAM['mysqliBindType']) {
                case self::MYSQLI_BIND_TYPE_DOUBLE:
                    $param = (double)$PARAM['value'];
                    break;
                case self::MYSQLI_BIND_TYPE_INTEGER:
                    $param = (int)$PARAM['value'];
                    break;
                case self::MYSQLI_BIND_TYPE_BOOL:
                    $param = (int)$PARAM['value'];
                    break;
                case self::MYSQLI_BIND_TYPE_STRING:
                default:
                    $param = (string)$PARAM['value'];
                    break;
            }

            $stmt->bind_param($PARAM['mysqliBindType'], $param);
        }

        $stmt->execute();
        return $stmt->get_result();
    }
}