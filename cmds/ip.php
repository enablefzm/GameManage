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
            // 获取能够获得添加的属性列表
            case 'addfield':
                $className = ob_gateway::cIP();
                $obRes = ob_conn_res::GetRes('IP_ADDFIELD');
                $obRes->SetDBs($className::getAddField()->getRes());
                return $obRes;
            // 添加IP地址
            case 'add':
                $className = ob_gateway::cIP();
                $obRes = ob_conn_res::GetRes('IP_ADD');
                if ($il < 2) {
                    $obRes->SetRes(false, '你要添加的IP地址呢？');
                } else {
                    $resVal = $className::add($args[1]);
                    if ($resVal == 0) {
                        $obRes->SetRes(true, "操作成功！");
                    } else {
                        $resMsg = '添加IP地址失败！';
                        switch ($resVal) {
                            case -1:
                                $resMsg = 'IP地址不正确';
                                break;
                            case -3:
                                $resMsg = 'IP地址已存在';
                                break;
                        }
                        $obRes->SetRes(false, $resMsg);
                    }
                }
                return $obRes;
            // 删除指定的IP地址
            case 'delete':
                $obRes = ob_conn_res::GetRes('IP_DELETE');
                if ($il < 2) {
                    $obRes->SetRes(false, '缺少你要删除的IP的ID。');
                } else {
                    $className = ob_gateway::cIP();
                    $blnOk = $className::delete($args[1]);
                    if ($blnOk) {
                        $obRes->SetRes(true, 'IP地址删除成功');
                    } else {
                        $obRes->SetRes(false, 'IP地址删除失败');
                    }
                }
                return $obRes;

        }
        return ob_conn_res::GetResAndSet('IP', false, '你要对IP进行什么操作？');
    }
}

?>
