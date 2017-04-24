<?php

class game implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il > 0) {
            switch ($args[0]) {
                case 'list':
                    $obs = ob_game::getGames();
                    $result = array();
                    foreach ($obs as $k => $ob) {
                        $arr      = $ob->getAttrib();
                        $arr['gameDate'] = date('Y-m-d', strtotime($arr['date']));
                        $result[] = array($arr['id'], $arr['gameName'], $arr['gameKey'], $arr['gameDate']);
                    }
                    $res = ob_conn_res::GetRes("GAMES");
                    $obResDb = new ob_res('游戏列表');
                    $obResDb->addMenu('系统ID', 100);
                    $obResDb->addMenu('游戏名称', 150);
                    $obResDb->addMenu('游戏键值', 150);
                    $obResDb->addMenu('创建时间', 0);
                    $obResDb->setDbs($result);
                    $res->SetDBs($obResDb->getRes());
                    return $res;
                    break;
                case 'zones':
                    $gameId = null;
                    if ($il >= 2) {
                        $gameId = $args[1];
                    } else {
                        $gameId = ob_session::GetSess()->getGameID();
                    }
                    if (!$gameId) {
                        return ob_conn_res::GetResAndSet("GAME_ZONES", false, '请先选择要操作的游戏！');
                    }
                    $obGame = ob_game::getGame($gameId);
                    $gateGame = ob_gateway::newGameOnID($obGame->getGameKey(), $obGame->getID());
                    $res = ob_conn_res::GetRes('GAME_ZONES');
                    $res->SetDBs($gateGame->getListZoneResDb());
                    return $res;
                    break;
                case 'set':
                    if ($il < 3)
                        return ob_conn_res::GetResAndSet("GAMESET", false, '选定游戏错误，需要参数！');
                    switch ($args[1]) {
                        case 'game':
                            $obGame = $this->setGame($args[2]);
                            if (!$obGame) {
                                return ob_conn_res::GetResAndSet("GAMESET", false, '选定的游戏不存在！');
                            }
                            ob_session::GetSess()->setGameID($obGame->getID());
                            ob_session::GetSess()->setZoneID(null);
                            $obRes = ob_conn_res::GetRes("GAMESET");
                            $obRes->SetDBs($obGame->getName());
                            return $obRes;
                            break;
                        case 'zone':
                            $obZone = ob_zone::getZone($args[2]);
                            if (!$obZone) {
                                return ob_conn_res::GetResAndSet("ZONESET", false, '要选定的ZONE不存在！');
                            }
                            // 获取游戏
                            $obGame = $this->setGame($obZone->getGameID());
                            if (!$obGame) {
                                return ob_conn_res::GetResAndSet("ZONESET", false, '要选定的ZONE指定的游戏不存在！');
                            }
                            // 写入GAME
                            $obSess = ob_session::GetSess();
                            $obSess->setGameZoneID($obGame->getID(), $obZone->getZoneID());
                            $obRes = ob_conn_res::GetRes("ZONESET");
                            $obRes->SetDBs(array($obGame->getName(), $obZone->getZoneName()));
                            return $obRes;
                            break;
                        default:
                            return ob_conn_res::GetResAndSet("GAMESET", false, '选定游戏错误，你要操作哪个选项？');
                    }
                    break;
            }
        }
        return ob_conn_res::GetResAndSet("GAME", false, '你要执行什么操作');
    }

    /**
     * 写入SESS里的游戏ID
     * @param integer $gameid
     */
    private function setGame($gameid) {
        $obGame = ob_game::getGame($gameid);
        if ($obGame == null) {
            return false;
        }
        return $obGame;
    }
}

?>