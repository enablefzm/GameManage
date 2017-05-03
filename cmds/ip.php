<?php

class ip implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 1)
            return ob_conn_res::GetResAndSet('IP', false, '缺少参数');
        switch ($args[0]) {
            case 'list':
                if ($il < 2) {
                    return ob_conn_res::GetResAndSet('IP_LIST', false, '缺少参数');
                }
                // return ob_conn_res::GetResAndSet('IP_LIST', false, '测试');
                $obRes = new ob_res('IP黑名单列表');
                $obRes->addMenu('ID', 0);
                $obRes->addMenu('IP地址', 0);
                $obRes->addDb(array(1, '192.168.0.1'));
                $obRes->addDb(array(2, '192.168.0.2'));
                $res = ob_conn_res::GetRes('IP_LIST');
                $res->SetDBs($obRes->getRes());
                return $res;
        }
        return ob_conn_res::GetResAndSet('IP', false, '你要对IP进行什么操作？');
    }
}

?>