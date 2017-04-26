<?php

class ob_conn_res {
    private $info = array(
        'CMD' => '',
        'RES' => true,
        'MSG' => '',
        'DBs' => null
    );

    public function __construct($cmd) {
        $this->info['CMD'] = $cmd;
    }

    public function SetDBs($resdb) {
        $this->info['DBs'] = $resdb;
    }

    public function SetRes($res, $msg) {
        if ($res) {
            $this->info['RES'] = true;
        } else {
            $this->info['RES'] = false;
        }
        $this->info['MSG'] = $msg;
    }

    public function ToJson() {
        return json_encode($this->info);
    }

    static public function GetRes($cmd) {
        return new ob_conn_res($cmd);
    }

    static public function GetResAndSet($cmd, $res, $msg) {
        $ob = self::GetRes($cmd);
        $ob->SetRes($res, $msg);
        return $ob;
    }

    /**
     * 创建系统级别的错误
     * @param msg $msg
     * @return ob_conn_res
     */
    static public function CreateSystemError($msg) {
        return self::GetResAndSet("SYSTEMERROR", false, $msg);
    }
}

?>
