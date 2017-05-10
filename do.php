<?php
require_once 'core.php';
require_once(__DIR__.'/gateway/smy/socket.php');
if (isset($_GET['cmd'])) {
    $obRes = ob_cmd::DoCmd($_GET['cmd']);
} else {
    $obRes = ob_conn_res::GetResAndSet('SYSTEM_MSG', false, '你要发送什么命令');
}
$res= $obRes->ToJson();
$cryTxt = \smy\socket::xorEnc($res, \smy\socket::XOR_KEY);
$uncryTxt = \smy\socket::xorEnc($cryTxt, \smy\socket::XOR_KEY);
echo '加密前：'.$res.'<br />';
echo '加密后：'.$cryTxt.'<br />';
echo '解密后：'.$uncryTxt.'<br />';
// echo $_SERVER['SERVER_ADDR'];
// foreach ($_SERVER as $k => $v) {
//     echo $k . '='. $v .'<br />';
// }
// echo gethostbyname($_SERVER['SERVER_NAME']);
?>
