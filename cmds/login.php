<?php

class login {
    static public function DoCmd($cmd, $args) {
        if (count($args) != 2) {
            $obRes = new ob_conn_res($cmd);
            $obRes->SetRes(false, "参数不够");
        }
    }
}
?>
