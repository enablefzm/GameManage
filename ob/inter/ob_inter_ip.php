<?php
interface ob_inter_ip {
    /**
     * 通过IP黑名单对象的系统ID获取对象
     * @param int $id
     * @return ob_inter_ip
     */
    static public function newIpForbidden($id);

    /**
     * 列出所有的黑名单信息
     * @param int $page
     * @param array $searchs
     * @return ob_res
     */
    static public function getList($page, $searchs);

    /**
     * 获取可以添加的字段属性
     * @return ob_res_ipfield
     */
    static public function getAddField();

    /**
     * 添加IP地址到数据库
     * @param string $ipVal
     * @return true | false
     */
    static public function add($ipVal);

    /**
     * 删除指定的IP地址
     * @param int $ipID
     * @return bool 成功为true 失败为false
     */
    static public function delete($ipID);
}
?>
