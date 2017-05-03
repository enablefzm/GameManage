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
}
?>
