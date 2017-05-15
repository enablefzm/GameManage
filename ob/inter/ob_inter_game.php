<?php
interface ob_inter_game {
    public function getListZoneResDb();

    /**
     * 发送邮件
     * @param int $zoneId
     * @param array $post
     * return array(bool, msg)
     */
    public function sendMail($zoneId, $post);
    /**
     * 查看指定帐号的角色数据
     * @param int    $zoneID
     * @param string $uid
     * @return array(bool, db)
     */
    public function seeRoles($zoneID, $uid);

    /**
     * 踢人下线
     * @param int    $zoneID
     * @param string $roleName
     * @return array(bool, msg)
     */
    public function kickRole($zoneID, $roleName);

    /**
     * 添加新Zone
     * @param string $strArgs
     * @return array(bool, msg)
     */
    public function addZone($strArgs);

    /**
     * 获取Zone的添加字段
     * @return \ob_res_fieldbase
     */
    public static function getZoneFields();

    /**
     * 获取发送邮件字段
     * @return \ob_res_fieldbase
     */
    public static function getSendMailField();
}

?>
