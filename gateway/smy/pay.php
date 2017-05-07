<?php
namespace smy;
require_once(__DIR__.'/connect.php');

class pay implements \ob_inter_pay {
    const TLB_NAME = 'zde_order';
    const GAME_ID  = 16;

    static private $obGame;
    static private $obZones = array();

    static public function getAllOrderList($page, $searchs = null) {
        $res = new \ob_res('充值列表');
        $res->addMenu('订单号', 0);
        $res->addMenu('帐号名称', 0);
        $res->addMenu('支付类型', 0);
        $res->addMenu('充值金额', 0);
        $res->addMenu('游戏名称', 0);
        $res->addMenu('分区名称', 0);
        $res->addMenu('创建时间', 0);
        $page = floor($page);
        if ($page < 1)
            $page = 1;
        $arrSearch = array('game_id='.self::GAME_ID, 'create_time > 1489027300');

        if (is_array($searchs)) {
            for ($i = 0; $i < count($searchs); $i++) {
                $arr = explode('=', $searchs[$i]);
                if (count($arr) == 2) {
                    switch ($arr[0]) {
                        case 'server_id':
                            $arrSearch[] = 'server_id='.$arr[1];
                            break;
                        case 'account':
                            $arrSearch[] = 'account LIKE "%'.$arr[1].'%"';
                            break;
                        case 'order_id':
                            $arrSearch[] = 'order_id LIKE"%'.$arr[1].'%"';
                            break;
                    }
                }
            }
        }
        $keys = (count($arrSearch) > 0) ? implode(' AND ', $arrSearch) : null;
        $rss = connect::GetPlatConn()->query(self::TLB_NAME, $keys, $page);
        foreach ($rss as $k => $rs) {
            $res->addDb(array($rs['order_id'], $rs['account'], self::getPayType($rs['pay_mode']), $rs['money'], self::getGameName(), self::getZoneName($rs['server_id']), date('Y-m-d H:i:s', $rs['create_time'])));
        }
        $max = ceil(connect::GetPlatConn()->count(self::TLB_NAME, $keys) / 30);
        $pages = \ob_feature::getPages($max, $page);
        $res->setPage($max, $pages, $page);
        return $res;
    }

    static private function getPayType($payType) {
        switch ($payType) {
            case 2:
                return '支付宝';
            case 17:
                return '微信';
            default:
                return '其它';
        }
    }

    static private function getGameName() {
        $obGame = self::getGame();
        if ($obGame == null) {
            return '_圣魔印';
        }
        return $obGame->getName();
    }

    static private function getGame() {
        if (!self::$obGame) {
            self::$obGame = \ob_game::getGame(1);
        }
        return self::$obGame;
    }

    static private function getZoneName($zoneID) {
        if (!isset(self::$obZones[$zoneID])) {
            $obGame = self::getGame();
            if ($obGame == null) {
                return '未知';
            }
            $obZone = $obGame->getZone($zoneID);
            if (!$obZone) {
                return '未知';
            }
            self::$obZones[$zoneID] = $obZone;
        }
        $obZone = self::$obZones[$zoneID];
        if (!$obZone) {
            return '未知';
        }
        return $obZone->getZoneName();
    }
}

?>