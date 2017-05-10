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
        $res->setKey(1);
        $arrSearch = array('game_id='.self::GAME_ID, 'create_time > 1489027300', 'status = 1');
        if (is_array($searchs)) {
            for ($i = 0; $i < count($searchs); $i++) {
                $arr = explode('=', $searchs[$i]);
                if (count($arr) == 2) {
                    switch ($arr[0]) {
                        case 'server_id':
                            $arrSearch[] = 'server_id='.$arr[1];
                            $res->setTitle('查看本服充值列表');
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

    /**
     * 统计这名玩家的具体充值金额
     * @param string $uid
     * @return int 充值合计金额
     */
    static public function getUidCount($uid) {
        $sql = 'SELECT sum(money) as cMoney FROM '.self::TLB_NAME.' WHERE account = "'.$uid.'" AND status = 1';
        $rss = connect::GetPlatConn()->querySql($sql);
        if (count($rss) < 1) {
            return 0;
        }
        return $rss[0]['cMoney'];
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_pay::getListSearchVal()
     */
    static public function getListSearchVal() {
        return array(
            'order_id'  => '订单号',
            'account'   => '帐号'
        );
    }

   static public function countMons($server_id) {
       $obRes = new \ob_res_countmons();
       $obRes->addMothsMenu('月份', 0);
       $obRes->addMothsMenu('充值总数', 0);
       $obRes->addMothsMenu('充值总额', 0);
       // SELECT DATE_FORMAT(FROM_UNIXTIME(create_time), "%Y-%m") as cMonth, count(order_id) as cOrder, sum(money) as cMoney FROM zde_order WHERE game_id=16 AND server_id=101 GROUP BY cMonth;
       $sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(create_time), "%Y-%m") as cMonth, count(order_id) as cOrder, sum(money) as cMoney FROM zde_order WHERE status=1 AND game_id=16 AND server_id='.$server_id.' GROUP BY cMonth';
       $rss = connect::GetPlatConn()->querySql($sql);
       $countOrder = 0;
       $countMoney = 0;
       for ($i = 0; $i < count($rss); $i++) {
           $rs = $rss[$i];
           $cOrder = $rs['cOrder'];
           $cMoney = $rs['cMoney'];
           $countOrder += $cOrder;
           $countMoney += $cMoney;
           $obRes->addMothsDb(array($rs['cMonth'], $cOrder, number_format($cMoney, 2)));
       }
       $obRes->addCountMenu('订单数总计', 200);
       $obRes->addCountMenu('充值总金额', 200);
       $obRes->addCountDb(array($countOrder, number_format($countMoney, 2)));
       return $obRes;
   }

   static public function countDays($server_id, $mon) {
       // SELECT DATE_FORMAT(FROM_UNIXTIME(create_time), "%Y-%m-%d") as cDays, COUNT(order_id) as cOrder, sum(money) as cMoney FROM zde_order WHERE game_id=16 AND server_id=101 AND DATE_FORMAT(FROM_UNIXTIME(create_time),"%Y-%m") = "2017-05" GROUP BY cDays;
       $obRes = new \ob_res($mon.'月的充值明细');
       $obRes->addMenu('日期', 150);
       $obRes->addMenu('充值总数', 150);
       $obRes->addMenu('充值总额', 0);
       $sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(create_time), "%Y-%m-%d") as cDays, COUNT(order_id) as cOrder, sum(money) as cMoney FROM zde_order WHERE status=1 AND game_id=16 AND server_id='.$server_id.' AND DATE_FORMAT(FROM_UNIXTIME(create_time),"%Y-%m") = "'.$mon.'" GROUP BY cDays';
       $rss = connect::GetPlatConn()->querySql($sql);
       for ($i = 0; $i < count($rss); $i++) {
           $rs = $rss[$i];
           $obRes->addDb(array($rs['cDays'], $rs['cOrder'], number_format($rs['cMoney'], 2)));
       }
       return $obRes;
   }
}

?>