<?php
require_once('config.php');
const PATH = dirname(__FILE__).'/';

// 自动加载类
function __autolaod($className) {
    $arr = explode('_', $className);
    $filePath = '';
    $il = count($arr);
    if ($il > 1) {
        for ($i = 0; $i < $il - 1; $i++) {
            $filePath .=  $arr[$i].'/';
        }
    }
    $fileName = PATH.$filePath.$className;
    require_once($fileName);
}
?>
