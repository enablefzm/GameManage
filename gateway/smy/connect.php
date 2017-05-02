<?php
namespace smy;
require_once(__DIR__.'/config.php');

class connect {
    // 定义平台数据库的连接对象
    static private $platConn;
    // 定义UC中心的数据库连接对象
    static private $ucConn;
    // 获得MySql数据库的连接

    /**
     * 返回Plat的数据库连接对象
     * @return \ob_conn_mysql
     */
    static public function GetPlatConn() {
        if (!self::$platConn) {
            self::$platConn = new \ob_conn_mysql(array(
                'DBName' => config::$PLAT_DB_DBNAME,
                'DBAddr' => config::$PLAT_DB_ADDER,
                'DBPort' => config::$PLAT_DB_PORT,
                'DBUser' => config::$PLAT_DB_USER,
                'DBPass' => config::$PLAT_DB_PWD
            ));
        }
        return self::$platConn;
    }

    /**
     * 返回UC的数据库连接对象
     * @return \ob_conn_mysql
     */
    static public function GetUcConn() {
        if (!self::$ucConn) {
            self::$ucConn = new \ob_conn_mysql(array(
                'DBName' => config::$UC_DB_DBNAME,
                'DBAddr' => config::$UC_DB_ADDER,
                'DBPort' => config::$UC_DB_PORT,
                'DBUser' => config::$UC_DB_USER,
                'DBPass' => config::$UC_DB_PWD
            ));
        }
        return self::$ucConn;
    }
}
?>
