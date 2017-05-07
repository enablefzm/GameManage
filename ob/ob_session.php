<?php

class ob_session {
    const SESS_UID_KEY      = 'GAMEMANAGE_UID';
    const SESS_GAME_ID      = 'GAMEMANAGE_GAME_ID';
    const SESS_GAME_ZONE_ID = 'GAMEMANAGE_GAME_ZONE_ID';

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

    public function getUid() {
        if (!$this->IsLogin())
            return null;
        return $_SESSION[self::SESS_UID_KEY];
    }

    public function setGameID($gameID) {
        $_SESSION[self::SESS_GAME_ID] = $gameID;
    }

    public function setGameZoneID($gameID, $gameZoneID) {
        $this->setGameID($gameID);
        $this->setZoneID($gameZoneID);
    }

    public function setZoneID($gameZoneID) {
        $_SESSION[self::SESS_GAME_ZONE_ID] = $gameZoneID;
    }

    public function getGameID() {
        if (isset($_SESSION[self::SESS_GAME_ID]))
            return $_SESSION[self::SESS_GAME_ID];
        return null;
    }

    public function getGameZoneID() {
        if (isset($_SESSION[self::SESS_GAME_ZONE_ID]))
            return $_SESSION[self::SESS_GAME_ZONE_ID];
        return null;
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

    /**
     * 获取SESSION里保存的ZoneID
     */
    static public function getZoneID() {
        return self::GetSess()->getGameZoneID();
    }

    // 获取已选择的游戏和Zone信息
    //  @return
    //      string  游戏名-分区名
    static public function getSelectGameAndZoneInfo() {
        $obSess = self::GetSess();
        $gameId = $obSess->getGameID();
        $result = array();
        if (!$gameId)
            return $result;
        $obGame = ob_game::getGame($gameId);
        if (!$obGame)
            return $result;
        $result['game'] = $obGame->getInfo();
        // 获取区名称
        $zId = $obSess->getGameZoneID();
        if (!$zId)
            return $result;
        $obZone = ob_zone::getZone($zId);
        if ($obZone) {
            $result['zone'] = $obZone->getBaseInfo();
        }
        return $result;
    }

    /**
     * 获得游戏SESS里选定的游戏名的Key
     *  @return
     *      null | string
     */
    static public function GetSelectGameKey() {
        $gameID = self::GetSess()->getGameID();
        if (!$gameID) {
            return null;
        }
        $obGame = ob_game::getGame(floor($gameID));
        if (!$obGame) {
            return null;
        }
        return $obGame->getGameKey();
    }
}

?>
