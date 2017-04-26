<?php
namespace smy;

class gameuser implements \ob_inter_gameuser {

    // 查询玩家帐号列表
    public function getListUserResDb($page) {
        $res = new \ob_res('玩家帐号列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('帐号UID', 0);
        $res->addMenu('手机号', 0);
        $res->addMenu('创建时间', 0);
        $res->addDb(array(1, 'enablefzm', '18150160101', '2017-03-09'));
        $res->addDb(array(2, 'jaxuu',     '18906050318', '2017-03-10'));
        return $res->getRes();
    }
}

?>