<?php
    $arr = array(
        'name' => 'jimmy',
        'age'  => 38,
        'sex'  => true
    );
    $arrs = array();
    foreach ($arr as  $k => $v) {
        $arrs[] = $k.'='.$v;
    }
    $arrs[] = 'IP='.$_SERVER['REMOTE_ADDR'];
    echo implode(', ', $arrs);
?>
