<?php

class ob_user {
    const TLB_Name = 'users';

    private $id;
    private $uid;
    private $password;
    private $name;

    public function __construct($db) {
        $this->id       = $db['id'];
        $this->uid      = $db['uid'];
        $this->password = $db['password'];
        $this->name     = $db['name'];
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

    // 通过用户UID构造用户对象
    // @parames
    //      uid string
    // @return
    //      CUser | null
    static public function GetUserInUid($uid) {
        $rss = ob_conn_connect::GetConn()->query(self::TLB_Name, 'uid="'.$uid.'"');
        if (count($rss) < 1)
            return null;
        return new ob_user($rss[0]);
    }
}

?>
