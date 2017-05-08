<?php

class pay implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 1) {
            return ob_conn_res::GetResAndSet('PAY', false, '缺少参数');
        }
        switch ($args[0]) {
            case 'list':
                if ($il < 2) {
                    return ob_conn_res::GetResAndSet('PAY_LIST', false, '缺少参数');
                }
                $cPay = ob_gateway::cPay();
                $search = array();
                if (isset($args[2])) {
                    if ($args[2] == 'true') {
                        // 获取当前服务器信息
                        $zoneID = ob_session::getZoneID();
                        if (!$zoneID)
                            return ob_conn_res::GetResAndSet('PAY_LIST', false, '请先选择要操作的游戏区');
                        $obZone = ob_zone::getZone($zoneID);
                        if (!$obZone)
                            return ob_conn_res::GetResAndSet('PAY_LIST', false, '你选择的分区不存在');
                        $areaID = $obZone->getZoneID();
                        $search[] = 'server_id='.$areaID;
                    }
                    if (isset($args[3])) {
                        $search[] = $args[3];
                    }
                }
                $res = $cPay::getAllOrderList($args[1], $search);
                $obRes = ob_conn_res::GetRes('PAY_LIST');
                $obRes->SetDBs($res->getRes());
                return $obRes;
                // 获取可以使用的查询Key
                case 'getsearch':
                    $payName = ob_gateway::cPay();
                    $obRes = ob_conn_res::GetRes('PAY_GETSEARCH');
                    $obRes->SetDBs($payName::getListSearchVal());
                    return $obRes;
                // 获取所有的各个月份数据
                case 'countmons':
                    $payName = ob_gateway::cPay();
                    $obRes = ob_conn_res::GetRes('PAY_COUNTMONS');
                    $obRes->SetDBs($payName::countMons()->getRes());
                    return $obRes;
        }
        return ob_conn_res::GetResAndSet('PAY', false, '没有具体的操作命令');
    }
}
?>
