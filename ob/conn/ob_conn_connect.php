<?php

class ob_conn_connect {
    static private $conn;

    static public function GetConn() {
        if (!self::$conn) {
            self::$conn = new ob_conn_mysql(array(
                'DBName' => SER_DBNAME,
                'DBAddr' => SER_ADDER,
                'DBPort' => SER_PORT,
                'DBUser' => SER_USER,
                'DBPass' => SER_PWD
            ));
        }
        return self::$conn;
    }
}

?>
