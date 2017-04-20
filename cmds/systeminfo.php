<?php

class systeminfo implements ob_ifcmd {

    public function doCmd($cmd, $args) {
        $res = ob_conn_res::GetRes('SYSTEMINFO');
        $res->SetDBs(array(
            'AppName' => APP_NAME,
            'AppVersion' => APP_VER
        ));
        return $res;
    }
}
?>
