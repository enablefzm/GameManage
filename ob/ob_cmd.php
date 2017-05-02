<?php
// 执行命令对象
class ob_cmd {

    // 执行命令
    //  @parames
    //      strCmd string 命令
    //  @return
    //      ob_conn_res
    static public function DoCmd($strCmd) {
        $arrs = explode(' ', $strCmd);
        if (count($arrs) > 30) {
            $obRes = ob_conn_res::GetResAndSet('CMDDO', false, '命令长度超过限度');
            return $obRes;
        }
        if (count($arrs) < 1) {
            $obRes = ob_conn_res::GetResAndSet('CMDDO', false, '没有任务指令');
            return $obRes;
        }
        $cmd    = array_shift($arrs);
        $args   = $arrs;
        $blnLog = ob_session::GetSess()->CheckIsLogin();
        // 判断命令是否需要特殊处理
        switch($cmd) {
            case 'login':
                if ($blnLog) {
                    $obRes  = ob_conn_res::GetRes($cmd);
                    $obRes->SetRes(false, "你已经登入系统");
                    return $obRes;
                }
                break;
            case 'systeminfo':
                break;
            default:
                if (!$blnLog) {
                    $obRes = ob_conn_res::GetRes("NOLOGIN");
                    $obRes->SetRes(false, '你还未登入系统');
                    return $obRes;
                }
        }
        // 执行登入
        $obCmd = self::GetCmd($cmd);
        if (!$obCmd) {
            $obRes = ob_conn_res::GetRes("NOCMD");
            $obRes->SetRes(false, '命令不存在');
        } else {
            $obRes = $obCmd->doCmd($cmd, $args);
        }
        return $obRes;
    }

    // 获取命令对象
    //  @parames
    //      string $cmd
    //  @return
    //      ob_ifcmd
    static private function GetCmd($cmd) {
        // 判断文件存不存在
        $file = __DIR__.'/../cmds/'.$cmd.'.php';
        if (!file_exists($file))
            return null;
        require_once $file;
        return new $cmd;
    }
}
?>
