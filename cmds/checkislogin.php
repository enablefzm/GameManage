<?php

class checkislogin implements ob_ifcmd {

    public function doCmd($cmd, $args) {
        $res = ob_conn_res::GetRes('CHECKISLOGIN');
        $blnLogin = ob_session::CheckIsLogin();
        $res->SetDBs($blnLogin);
        return $res;
    }
}

?>