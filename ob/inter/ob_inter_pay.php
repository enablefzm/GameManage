<?php

interface ob_inter_pay {
    /**
     * 获取所有充值ORDER
     * @param int $page
     * @param string $searchs
     * @return ob_res
     */
    static public function getAllOrderList($page, $searchs = null);

}
?>
