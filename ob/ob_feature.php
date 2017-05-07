<?php
// 功能对象
class ob_feature {

    /**
     * 判断是不是IP地址
     * @param  string $ipadder
     * @return boolean 如果是IP地址则返回true 反之返回false
     */
    static public function isIpAdder($ipadder) {
        return filter_var($ipadder, FILTER_VALIDATE_IP);
    }

    /**
     * 获取页面排列
     * @param int $max
     * @param int $page
     * @param int $showPage
     * @return array $pages
     */
    static public function getPages($max, $page, $showPage = 12) {
        $pages = array();
        if ($max > $showPage) {
            $minPage = $page - 6;
            $maxPage = $page + 6;
            for (; $minPage < 1; $minPage++) {
                $maxPage ++;
            }
            for (; $maxPage > $max; $maxPage--) {
                $minPage --;
            }
            for ($i = $minPage; $i <= $maxPage; $i++) {
                $pages[] = $i;
            }
        } else {
            for ($i = 1; $i <= $max; $i++) {
                $pages[] = $i;
            }
        }
        return $pages;
    }
}
?>
