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
                    $obResDb->addMenu('系统ID', 0);
                    $obResDb->addMenu('游戏名称', 0);
                    $obResDb->addMenu('游戏键值', 0);
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
                case 'zonefield':
                    $cGameName = ob_gateway::CGame();
                    $res = $cGameName::getZoneFields();
                    $obRes = ob_conn_res::GetRes('ZONE_FIELD');
                    $obRes->SetDBs($res->getRes());
                    return $obRes;
                    break;
                case 'mailfield':
                    $cGameName = ob_gateway::CGame();
                    $res = $cGameName::getSendMailField();
                    $obRes = ob_conn_res::GetRes('MAIL_FIELD');
                    $obRes->SetDBs($res->getRes());
                    return $obRes;
                    break;
                case 'addzone':
                    if ($il < 2)
                        return ob_conn_res::GetResAndSet('GAME_ADDZONE', false, '缺少参数');
                    $arr = $this->addZone($args[1]);
                    return ob_conn_res::GetResAndSet('GAME_ADDZONE', $arr[0], $arr[1]);
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
                            $obRes->SetDBs(ob_session::getSelectGameAndZoneInfo());
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
                            $obSess->setGameZoneID($obGame->getID(), $obZone->getID());
                            $obRes = ob_conn_res::GetRes("ZONESET");
                            $obRes->SetDBs(ob_session::getSelectGameAndZoneInfo());
                            return $obRes;
                            break;
                        default:
                            return ob_conn_res::GetResAndSet("GAMESET", false, '选定游戏错误，你要操作哪个选项？');
                    }
                    break;
                // 踢人下线
                case 'kick':
                    if ($il < 2) {
                        return ob_conn_res::GetResAndSet('GAME_KICK', false, '缺少参数');
                    }
                    // 获取当前ZoneID
                    $zoneID = ob_session::getZoneID();
                    if (!$zoneID)
                        return ob_conn_res::GetResAndSet('GAME_KICK', false, '你先选择要操作的游戏分区');
                    $roleName = $args[1];
                    $obGame = ob_gateway::newGameOnID(ob_session::GetSelectGameKey(), ob_session::GetSess()->getGameID());
                    $result = $obGame->kickRole($zoneID, $roleName);
                    return ob_conn_res::GetResAndSet('GAME_KICK', $result[0], $result[1]);
                    break;
                // 查看角色信息
                case 'seerole':
                    if ($il < 2)
                        return ob_conn_res::GetResAndSet('GAME_SEEROLE', false, '缺少参数');
                    $zoneID = ob_session::getZoneID();
                    if (!$zoneID)
                        return ob_conn_res::GetResAndSet('GAME_SEEROLE', false, '你先选择要操作的游戏分区');
                    $uid = $args[1];
                    $obGame = ob_gateway::newGameOnID(ob_session::GetSelectGameKey(), ob_session::GetSess()->getGameID());
                    $result = $obGame->seeRoles($zoneID, $uid);
                    if (!$result[0])
                        return ob_conn_res::GetResAndSet('GAME_SEEROLE', false, $result[1]);
                    $res = $result[1];
                    $obRes = ob_conn_res::GetResAndSet('GAME_SEEROLE', true, '');
                    $obRes->SetDBs($res->getRes());
                    return $obRes;
                    break;
                // 发送邮件
                case 'sendmail':
                    $zoneID = ob_session::getZoneID();
                    if (!$zoneID)
                        return ob_conn_res::GetResAndSet('GAME_SENDMAIL', false, '你先选择要操作的游戏分区');
                    $obGame = ob_gateway::newGameOnID(ob_session::GetSelectGameKey(), ob_session::GetSess()->getGameID());
                    $result = $obGame->sendMail($zoneID, $_POST);
                    return ob_conn_res::GetResAndSet('GAME_SENDMAIL', $result[0], $result[1]);
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

    /**
     * 增加新的分区Zone信息
     * @param string $args
     * @return array(bool, msg)
     */
    private function addZone($args) {
        // 获取默认的游戏对象
        $key = ob_session::GetSelectGameKey();
        $id  = ob_session::GetSess()->getGameID();
        $obGame = ob_gateway::newGameOnID($key, $id);
        $result = $obGame->addZone($args);
        if ($result < 1) {
            switch ($result) {
                case -1:
                    return array(false, '错误的参数'.$result);
                case -2:
                    return array(false, '已存在这个分区ID'.$result);
                default:
                    return array(false, '未知错误'.$result);
            }
        } else {
            return array(true, '操作成功！');
        }
    }
}

?>