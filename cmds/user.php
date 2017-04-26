<?php

class user implements ob_ifcmd {

    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 1) {
            return ob_conn_res::GetResAndSet("USER", false, '参数不足');
        }
        switch ($args[0]) {
            case 'info':
                $uid    = ob_session::GetSess()->getUid();
                if (!$uid) {
                    return ob_conn_res::GetResAndSet("USERINFO", false, '获取UID失败');
                }
                $obUser = ob_user::GetUserInUid($uid);
                $obRes  = ob_conn_res::GetRes("USERINFO");
                $userInfo = $obUser->GetUserInfo();
                // 获取已选中的Zone信息
                $userInfo['SelectGameInfo'] = ob_session::getSelectGameAndZoneInfo();
                $obRes->SetDBs($userInfo);
                return $obRes;
                break;
        }
        return ob_conn_res::GetResAndSet("USER", false, '没有具体要的操作');
    }
}

?>