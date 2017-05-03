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
                $className = ob_gateway::cIP();
                $resList = $className::getList($args[1], array());
                $obRes = ob_conn_res::GetRes('IP_LIST');
                $obRes->SetDBs($resList->getRes());
                return $obRes;

        }
        return ob_conn_res::GetResAndSet('IP', false, '你要对IP进行什么操作？');
    }
}

?>