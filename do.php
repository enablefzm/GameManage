<?php
require_once 'core.php';
if (isset($_GET['cmd'])) {
    $obRes = ob_cmd::DoCmd($_GET['cmd']);
} else {
    $obRes = ob_conn_res::GetResAndSet('SYSTEM_MSG', false, '你要发送什么命令');
}
echo $obRes->ToJson();
?>