<?php

class gameuser implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 2) {
            return ob_conn_res::GetResAndSet("GAMEUSER", false, '参数不足！');
        }
        $page = floor($args[0]);
        if ($page < 1) {
            $page = 1;
        }
        switch ($args[0]) {
            case 'list':
                // 通过网关获取对象
                $obGameUser = ob_gateway::newGameUserOnSess();
                $obRes = ob_conn_res::GetRes('GAMEUSER_LIST');
                $obRes->SetDBs($obGameUser->getListUserResDb($page));
                return $obRes;
        }
        return ob_conn_res::GetResAndSet("GAMEUSER", false, '你想要对玩家帐号做什么？！');
    }
}

?>