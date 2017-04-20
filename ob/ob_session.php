<?php

class ob_session {
    const SESS_UID_KEY = 'GAMEMANAGE_UID';
    static private $obSess;

    private function __construct() {
        @ session_start();
    }

    public function IsLogin() {
        if (isset($_SESSION[self::SESS_UID_KEY]))
            return true;
        return false;
    }

    public function setUid($uid) {
        $_SESSION[self::SESS_UID_KEY] = $uid;
    }

    public function setLogin($uid) {
        $this->setUid($uid);
    }

    public function destroy() {
        unset($_SESSION[self::SESS_UID_KEY]);
    }

    // 获得Session对象
    //  @return
    //      ob_session
    static public function GetSess() {
        if (!self::$obSess) {
            self::$obSess = new ob_session();
        }
        return self::$obSess;
    }

    // 判断是否登入系统
    // @return
    //  boolean  成功返回true
    static public function CheckIsLogin() {
        return self::GetSess()->IsLogin();
    }

    // 执行登入操作
    //  @parames
    //      uid string 用户帐号
    static public function DoLogin($uid) {
        self::GetSess()->setUid($uid);
    }
}

?>
