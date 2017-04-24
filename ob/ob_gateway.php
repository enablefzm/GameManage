<?php

class ob_gateway {
    /**
     * 加载指定接口对象文件，如果接口文件不存在则停止运行（这个是严重的错误）
     * @param string $type
     * @param string $actName
     * @return boolean
     */
    static public function requireGateWay($type, $actName) {
        // 判断文件存不存在
        $file = __DIR__.'/../gateway/'.$type.'/'.$actName.'.php';
        if (!file_exists($file)) {
            $res = ob_conn_res::GetResAndSet("SYSTEM", false, '错误：接口类型'.$type.'的'.$actName.'不存在。');
            echo $res->ToJson();
            die(0);
            return false;
        }
        require_once $file;
        return true;
    }

    /**
     * 获取Zone接口对象
     * @param string $gameKey
     * @param array $rs
     * @return ob_inter_zone
     */
    static public function newZone($gameKey, $rs) {
        if (!self::requireGateWay($gameKey, 'zone')) {
            return null;
        }
        $obname = '\\'.$gameKey.'\\zone';
        return new $obname($rs);
    }

    /**
     * 获取游戏接口对象
     * @param string $gamekey
     * @param integer $id
     * @return NULL|ob_inter_game
     */
    static public function newGameOnID($gamekey, $id) {
        if (!self::requireGateWay($gamekey, 'game')) {
            return null;
        }
        $rss = ob_conn_connect::GetConn()->query("games", 'id='.$id);
        if (count($rss) != 1) {
            return null;
        }
        $obname = '\\'.$gamekey.'\\game';
        return new $obname($rss[0]);
    }
}

?>