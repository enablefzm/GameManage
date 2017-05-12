<?php
interface ob_inter_game {
    public function getListZoneResDb();

    /**
     * 获取Zone的添加字段
     * @return \ob_res_fieldbase
     */
    public static function getZoneFields();
}

?>