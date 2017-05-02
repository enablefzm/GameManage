<?php

class gameuser implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 1) {
            return ob_conn_res::GetResAndSet("GAMEUSER", false, '参数不足！');
        }
        switch ($args[0]) {
            case 'list':
            case 'disuser':
                if ($il < 2)
                    return ob_conn_res::GetResAndSet("GAMEUSER", false, '查询参数不足！');
                $page = floor($args[1]);
                if ($page < 1) {
                    $page = 1;
                }
                $search = null;
                if (isset($args[2])) {
                    $search = $args[2];
                }

                return $this->getGameList($page, $search);
            // 查询玩家帐号具体信息
            case 'see':
                if ($il < 2)
                    return ob_conn_res::GetResAndSet('GAMEUSER_SEE', false, '请指定要查询的帐号ID');
                $id = floor($args[1]);
                $cGameUser = ob_gateway::gameUserOnSess();
                $obGameUser = $cGameUser::newGameUser($id);
                $obRes = ob_conn_res::GetRes('GAMEUSER_SEE');
                $obRes->SetDBs($obGameUser->getUserInfo()->getRes());
                return $obRes;
            // 获取可以使用的查询Key
            case 'getsearch':
                $obGameUser = ob_gateway::gameUserOnSess();
                $obRes = ob_conn_res::GetRes('GAMEUSER_GETSEARCH');
                $obRes->SetDBs($obGameUser::getListSearchVal());
                return $obRes;
            // 执行密码修改
            case 'editpass':
                $keyName = 'GAMEUSER_EDITPASS';
                if ($il != 3) {
                    return ob_conn_res::GetResAndSet($keyName, false, '修改密码参数不足');
                }
                $uid = $args[1];
                $newPwd = $args[2];
                $obGameName = ob_gateway::gameUserOnSess();
                $obUser = $obGameName::newGameUser($uid);
                if (!$obUser) {
                    return ob_conn_res::GetResAndSet($keyName, false, '你要修改密码的玩家不存在！');
                }
                $blnOK = $obUser->updatePassword($newPwd);
                if (!$blnOK) {
                    return ob_conn_res::GetResAndSet($keyName, false, '修改密码操作失败！ -_-!');
                }
                return ob_conn_res::GetResAndSet($keyName, true, '修改密码操作成功！');

        }
        return ob_conn_res::GetResAndSet("GAMEUSER", false, '你想要对玩家帐号做什么？！');
    }

    private function getGameList($page, $search) {
        $keyName = 'GAMEUSER_LIST';
        // 通过SESS里获得网关操作对象
        $obGameUser = ob_gateway::gameUserOnSess();
        $obRes = ob_conn_res::GetRes($keyName);
        $obRes->SetDBs($obGameUser::getListUserResDb($page, $search)->getRes());
        return $obRes;
    }
}
?>
