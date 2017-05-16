<?php

class ob_log {
    const TLB_NAME = 'log';

    public static function loginLog($uid, $msg) {
        self::log($uid, 'LOGIN', $msg);
    }

    public static function logAct($act, $msg) {
        $uid = ob_session::GetSess()->getUid();
        self::log($uid, $act, $msg);
    }

    public static function log($uid, $act, $msg) {
        $saveArray = array(
            'uid'     => $uid,
            'logtime' => date('Y-m-d H:i:s'),
            'ipaddr'  => $_SERVER['REMOTE_ADDR'],
            'act'     => $act,
            'msg'     => $msg
        );
        ob_conn_connect::GetConn()->updata(self::TLB_NAME, null, $saveArray, true);
    }
}

?>
