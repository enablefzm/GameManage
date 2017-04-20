<?php
require_once('config.php');
define("PATH", dirname(__FILE__).'/');

// 注册自动加载类
spl_autoload_register(function ($className) {
    $arr = explode('_', $className);
    $filePath = '';
    $il = count($arr);
    if ($il > 1) {
        for ($i = 0; $i < $il - 1; $i++) {
            $filePath .=  $arr[$i].'/';
        }
    }
    $fileName = PATH.$filePath.$className.'.php';
    require_once($fileName);
});
?>
