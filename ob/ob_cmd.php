<?php
// 执行命令对象
class ob_cmd {

    // 执行命令
    //  @parames
    //      strCmd string 命令
    //  @return
    //      ob_conn_res
    static pulbic function Do($strCmd) {
        $arrs = explode(' ', $strCmd);
        if (count($arrs) > 30) {
            $obRes = ob_conn_res::GetResAndSet('CMDDO', false, '命令长度超过限度');
            return $obRes;
        }
        if (count($arrs) < 1) {
            $obRes = ob_conn_res::GetResAndSet('CMDDO', false, '没有任务指令');
            return $obRes;
        }
        $cmd = $arrs[0];
        // 判断是否登入
        if ($cmd == 'login') {
            $blnLog = ob_session::GetSess()->CheckIsLogin();
            $obRes  = self::GetRes($cmd);
            if ($blnLog) {
                $obRes->SetRes(false, "你已经登入系统");
            } else {
                // 执行登入

            }
        }
    }
}

?>
