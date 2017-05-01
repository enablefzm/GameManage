<?php
namespace smy;
require_once(__DIR__.'/config.php');

class connect {
    // 定义平台数据库的连接对象
    static private $platConn;

    // 获得MySql数据库的连接
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
}
?>
