<?php

class set implements ob_ifcmd {
    public function doCmd($cmd, $args) {
        $il = count($args);
        if ($il < 2) {
            return ob_conn_res::GetResAndSet("SET", false, "缺少参数");
        }
        switch ($args[0]) {
            case 'game':
                break;
            case 'zone':
                break;

        }
        return ob_conn_res::GetResAndSet("SET", false, "你要执行什么操作");
    }
}

?>