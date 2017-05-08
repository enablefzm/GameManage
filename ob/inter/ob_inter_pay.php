<?php

interface ob_inter_pay {
    /**
     * 获取所有充值ORDER
     * @param int $page
     * @param string $searchs
     * @return ob_res
     */
    static public function getAllOrderList($page, $searchs = null);

    /**
     * 获取充值帐号可以查询的条件
     *  array(key: text)
     * @return array()
     */
    static public function getListSearchVal();

    /**
     * 获得充值的所有月份数据
     * @return ob_res_countmons
     */
    static public function countMons($zoneID);

    /**
     * 获得充值的某个月的数据
     * @param int $mon
     * @return ob_res
     */
    static public function countDays($zoneID, $mon);
}
?>
