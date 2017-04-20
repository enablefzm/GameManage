<?php

class loginout implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $obRes = ob_conn_res::GetResAndSet("LOGINOUT", true, '');
        $obSess = ob_session::GetSess();
        $obSess->destroy();
        return $obRes;
    }
}

?>