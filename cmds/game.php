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
                    $gameId = 1;
                    if ($il >= 2) {
                        $gameId = $args[1];
                    }
                    $obGame = ob_game::getGame($gameId);
                    $gateGame = ob_gateway::newGameOnID($obGame->getGameKey(), $obGame->getID());
                    $res = ob_conn_res::GetRes('GAME_ZONES');
                    $res->SetDBs($gateGame->getListZoneResDb());
                    return $res;
                    break;
            }
        }
        return ob_conn_res::GetResAndSet("GAME", false, '你要执行什么操作');
    }
}

?>