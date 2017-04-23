<?php

class game implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il > 0) {
            switch ($args[0]) {
                case 'list':
                    $obs = ob_games::getGames();
                    $result = array();
                    foreach ($obs as $k => $ob) {
                        $arr      = $ob->getAttrib();
                        $arr['gameDate'] = date('Y-m-d', strtotime($arr['date']));
                        $result[] = array($arr['id'], $arr['gameName'], $arr['gameKey'], $arr['gameDate']);
                    }
                    $res = ob_conn_res::GetRes("GAMES");
                    $resDb = array(
                        'title' => '游戏列表',
                        'menus' => array(
                            array('系统ID', 200),
                            array('游戏名称', 200),
                            array('游戏键值', 200),
                            array('创建时间', 0)
                        ),
                        'dbs' => $result,
                        'key' => 0
                    );
                    $res->SetDBs($resDb);
                    return $res;
                    break;
            }
        }
        return ob_conn_res::GetResAndSet("GAME", false, '你要执行什么操作');
    }
}

?>