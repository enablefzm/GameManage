<?php
namespace smy;
require_once(__DIR__.'/connect.php');

class gameuser implements \ob_inter_gameuser {
    static private $tlbName = 'zde_members';

    /**
     * 获得玩家具体信息
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getUserInfo()
     */
    public function getUserInfo() {
        $obRes = new \ob_gameuserres('1');
        $obRes->addFunc(\ob_gameuserres::FUN_EDIT_PASS);
        $obRes->addDb(\ob_gameuserres::TEXT, '系统ID', '2');
        $obRes->addDb(\ob_gameuserres::TEXT, '帐号UID', 'jimmyFan');
        $obRes->addDb(\ob_gameuserres::TEXT, '手机号', '18150160101');
        $obRes->addDb(\ob_gameuserres::TEXT, 'QQ', '5123736');
        return $obRes;
    }

    static private function getPages($max, $page) {
        $pages = array();
        if ($max > 12) {
            $minPage = $page - 6;
            $maxPage = $page + 6;
            for (; $minPage < 1; $minPage++) {
                $maxPage ++;
            }
            for (; $maxPage > $max; $maxPage--) {
                $minPage --;
            }
            for ($i = $minPage; $i <= $maxPage; $i++) {
                $pages[] = $i;
            }
        } else {
            for ($i = 1; $i <= $max; $i++) {
                $pages[] = $i;
            }
        }
        return $pages;
    }

    /**
     * 查询玩家帐号列表
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListUserResDb()
     */
    static public function getListUserResDb($page, $search) {
        $res = new \ob_res('玩家帐号列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('帐号UID', 0);
        $res->addMenu('姓名', 0);
        $res->addMenu('Email', 0);
        $res->addMenu('手机号', 0);
        $res->addMenu('注册时间', 0);
        $keys = null;
        if ($search) {
            $arrSearch = explode('=', $search);
            if (count($arrSearch) == 2) {
                switch ($arrSearch[0]) {
                    case 'uid':
                        $keys = 'username LIKE "%'.$arrSearch[1].'%"';
                        break;
                    case 'name':
                        $keys = 'name LIKE "%'.$arrSearch[1].'%"';
                        break;
                }
            }
        }
        $rss = connect::GetPlatConn()->query(self::$tlbName, $keys, $page);
        foreach ($rss as $k => $rs) {
            $res->addDb(array($rs['uid'], $rs['username'], $rs['name'], $rs['email'], $rs['phone'], date('Y-m-d', $rs['reg_time'])));
        }
        $max = ceil(connect::GetPlatConn()->count(self::$tlbName, $keys) / 30);
        $pages = self::getPages($max, $page);
        $res->setPage($max, $pages, $page);
        return $res;
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListSearchVal()
     */
    static public function getListSearchVal() {
        return array(
            'uid' => '玩家帐号',
            // 'name' => '玩家姓名'
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