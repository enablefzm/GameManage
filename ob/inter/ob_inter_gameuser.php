<?php

interface ob_inter_gameuser {
    /**
     * 查询玩家帐号的具体信息
     * @param integer $page
     * @param array $searchs
     *        {
     *          key => val
     *        }
     * @return ob_res
     */
    static public function getListUserResDb($page, $searchs);

    /**
     * 获取玩家帐号可以查询的条件
     *  array(key: text)
     * @return array()
     */
    static public function getListSearchVal();

    /**
     * 创建一个玩家帐号对象
     * @param integer $guid
     * @return ob_inter_gameuser
     */
    static public function newGameUser($guid);

    /**
     * 获取玩家对象信息
     * @return ob_gameuserres
     */
    public function getUserInfo();

    /**
     *  修改玩家帐号的密码
     *  @param string $newpwd 玩家帐号的新密码
     *  @return boolean true 表示操作成功
     */
    public function updatePassword($newpwd);

    /**
     * 切换玩家封号状态
     * @return boolea
     */
    public function setForbidden();
}

?>