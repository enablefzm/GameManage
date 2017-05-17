<?php

class ob_user {
    const TLB_Name = 'users';

    private $id;
    private $uid;
    private $password;
    private $name;
    private $level;

    public function __construct($db) {
        $this->id       = $db['id'];
        $this->uid      = $db['uid'];
        $this->password = $db['password'];
        $this->name     = $db['name'];
        $this->level    = $db['level'];
    }

    // 获取用户名
    //  @return
    //      string
    public function GetUserName() {
        return $this->name;
    }

    // 获取用户详细名称
    //  @return
    //      Array  用户信息
    public function GetUserInfo() {
        return array(
            'id'   => $this->id,
            'uid'  => $this->uid,
            'name' => $this->name
        );
    }

    public function getLevel() {
        return $this->level;
    }

    // 判断密码
    //  @parames
    //      string $password
    //  @return
    //      boolean 成功返回true
    public function chekcPass($password) {
        if ($this->password != $password) {
            return false;
        }
        return true;
    }

    /**
     * 更新密码
     * @param string $newPassword
     */
    public function updatePass($newPassword) {
        $newPassword = str_replace(array(" ", "*"), array("", ""), $newPassword);
        if (strlen($newPassword) > 30 || strlen($newPassword) < 1)
            return false;
        $this->password = $newPassword;
        $res = ob_conn_connect::GetConn()->updata(self::TLB_Name, 'id='.$this->id, array('password' => $this->password));
        return true;
    }

    // 通过用户UID构造用户对象
    // @parames
    //      uid string
    // @return
    //      CUser | null
    static public function GetUserInUid($uid) {
        $uid = str_replace(array(" ", "*"), array("", ""), $uid);
        $rss = ob_conn_connect::GetConn()->query(self::TLB_Name, 'uid="'.$uid.'"');
        if (count($rss) < 1)
            return null;
        return new ob_user($rss[0]);
    }

    /**
     * 获取用户列表
     * @return ob_res
     */
    static public function GetUserList() {
        $rss = ob_conn_connect::GetConn()->query(self::TLB_Name);
        $res = new ob_res('用户列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('帐号',   0);
        $res->addMenu('姓名',   0);
        $res->setKey(1);
        foreach($rss as $k => $rs) {
            if ($rs['id'] == 1)
                continue;
            $res->addDb(array($rs['id'], $rs['uid'], $rs['name']));
        }
        return $res;
    }

    /**
     * 增加新用户
     * @param string $parames
     * @return array array(bool, msg)
     */
    static public function addUser($parames) {
        $arrs = explode(',', $parames);
        $saveInfo = array();
        foreach ($arrs as $k => $v) {
            $arr = explode('=', $v);
            if (count($arr) != 2)
                continue;
            switch ($arr[0]) {
                case 'uid':
                    $val = $arr[1];
                    $val = self::formatStr($val);
                    if (strlen($val) < 2 || strlen($val) > 30) {
                        return array(false, '用户帐号名长度必须在2-30之间');
                    }
                    $saveInfo['uid'] = $val;
                    break;
                case 'name':
                    $val = $arr[1];
                    $val = self::formatStr($val);
                    if (strlen($val) < 2 || strlen($val) > 30) {
                        return array(false, '用户姓名长度必须在2-10之间');
                    }
                    $saveInfo['name'] = $val;
                    break;
                case 'pass':
                    $val = $arr[1];
                    $val = self::formatStr($val);
                    if (strlen($val) < 2 || strlen($val) > 30) {
                        return array(false, '用户密码长度必须在2-30之间');
                    }
                    $saveInfo['password'] = $val;
                    break;
            }
        }
        if (count($saveInfo) != 3) {
            return array(false, '参数不全');
        }
        ob_conn_connect::GetConn()->updata(self::TLB_Name, null, $saveInfo, true);
        return array(true, '增加成功！');
    }

    static public function delUser($uid) {
        $uid = self::formatStr($uid);
        if (strlen($uid) < 1 || strlen($uid) > 30) {
            return array(false, '用户UID参数错误！');
        }
        $res = ob_conn_connect::GetConn()->delete(self::TLB_Name, 'uid="'.$uid.'"');
        if ($res > 0) {
            return array(true, '删除用户成功！');
        } else {
            return array(false, '删除用户失败！！');
        }
    }

    static private function formatStr($str) {
        return str_replace(array(" ", "*"), array("", ""), $str);
    }
}

?>
