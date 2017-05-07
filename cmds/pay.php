<?php

class pay implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 2) {
            return ob_conn_res::GetResAndSet('PAY', false, '缺少参数');
        }
        switch ($args[0]) {
            case 'list':
                $cPay = ob_gateway::cPay();
                $res = $cPay::getAllOrderList($args[1]);
                $obRes = ob_conn_res::GetRes('PAY_LIST');
                $obRes->SetDBs($res->getRes());
                return $obRes;
        }
        return ob_conn_res::GetResAndSet('PAY', false, '没有具体的操作命令');
    }
}
?>
