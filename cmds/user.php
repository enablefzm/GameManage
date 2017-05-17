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
            case 'list':
                $res = ob_user::GetUserList();
                $obRes = ob_conn_res::GetRes('USERLIST');
                $obRes->SetDBs($res->getRes());
                return $obRes;
                break;
            case 'add':
                $keyName = 'USER_ADD';
                if ($il < 2)
                    return ob_conn_res::GetResAndSet($keyName, false, '缺少参数');
                if (!$this->checkLevel())
                    return ob_conn_res::GetResAndSet($keyName, false, '你不能执行增加用户操作');
                $arrResult = ob_user::addUser($args[1]);
                return ob_conn_res::GetResAndSet($keyName, $arrResult[0], $arrResult[1]);
                break;
            // 更新用户密码
            // uid, newpass
            case 'uppass':
                $keyName = 'USER_UPPASS';
                if (!$this->checkLevel())
                    return ob_conn_res::GetResAndSet($keyName, false, '你不能执行修改用户密码操作！');
                if ($il < 3)
                    return ob_conn_res::GetResAndSet($keyName, false, '参数不全！');
                $obUser = ob_user::GetUserInUid($args[1]);
                if (!$obUser)
                    return ob_conn_res::GetResAndSet($keyName, false, '用户不存在！');
                $res = $obUser->updatePass($args[2]);
                if ($res)
                    return ob_conn_res::GetResAndSet($keyName, true, '修改密码成功！');
                else
                    return ob_conn_res::GetResAndSet($keyName, false, '修改密码失败！');
                break;
            case 'del':
                $keyName = 'USER_DEL';
                if ($il < 2)
                    return ob_conn_res::GetResAndSet($keyName, false, '缺少参数');
                if (!$this->checkLevel())
                    return ob_conn_res::GetResAndSet($keyName, false, '你不能执行删除用户操作！');
                if (trim($args[1]) == ob_session::GetSess()->getUid())
                    return ob_conn_res::GetResAndSet($keyName, false, '你不能删除自己！');
                $arr = ob_user::delUser($args[1]);
                return ob_conn_res::GetResAndSet($keyName, $arr[0], $arr[1]);
                break;
        }
        return ob_conn_res::GetResAndSet("USER", false, '没有具体要的操作！');
    }

    // 判断当前用户是否可以操作
    private function checkLevel() {
        // return false;
        $obUser = ob_user::GetUserInUid(ob_session::GetSess()->getUid());
        if (!$obUser) {
            return false;
        }
        if ($obUser->getLevel() < 9)
            return false;
        return true;
    }
}
?>
