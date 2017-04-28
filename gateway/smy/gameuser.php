<?php
namespace smy;

class gameuser implements \ob_inter_gameuser {

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getUserInfo()
     */
    public function getUserInfo() {
        $obRes = new \ob_gameuserres('1');
        $obRes->addFunc(\ob_gameuserres::FUN_EDIT_PASS);
        $obRes->addDb(\ob_gameuserres::TEXT, '系统ID', '1');
        $obRes->addDb(\ob_gameuserres::TEXT, '帐号UID', 'enablefzm');
        $obRes->addDb(\ob_gameuserres::TEXT, '手机号', '18150160101');
        return $obRes;
    }

    /**
     * 查询玩家帐号列表
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListUserResDb()
     */
    static public function getListUserResDb($page, $sears) {
        $res = new \ob_res('玩家帐号列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('帐号UID', 0);
        $res->addMenu('手机号', 0);
        $res->addMenu('创建时间', 0);
        $res->addDb(array(1, 'enablefzm', '18150160101', '2017-03-09'));
        $res->addDb(array(2, 'jaxuu',     '18906050318', '2017-03-10'));
        return $res;
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListSearchVal()
     */
    static public function getListSearchVal() {
        return array(
            'uid' => '玩家帐号',
            'phone' => '手机号',
            'oicq'  => '玩家QQ号'
        );
    }

    /**
     * 创建一个玩家帐号实例
     * !CodeTemplates.overridecomment.nonjd!
     * @see \ob_inter_gameuser::newGameUser
     */
    static public function newGameUser($guid) {
        // 具体去获取相应的玩家对象
        return new gameuser();
    }
}

?>