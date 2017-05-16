<?php
/**
 * Excel文件对象
 * @author Jimmy
 *
 */
require_once(__DIR__.'/../gateway/smy/connect.php');
use smy\connect;

class ob_excel {
    private $year     = '2017';
    private $month    = '05';
    private $arrTitle = array('UID', '姓名', '手机号', 'QQ', 'Email', '注册时间');
    private $excel    = '';

    public function __construct($year, $month) {
        $this->year  = $year;
        $this->month = $month;
    }
    public function showExcel() {
        ob_log::logAct("DOWN_EXCEL", '导出'.$this->year.'年'.$this->month.'月份玩家数据');
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=userlist_".$this->year."_".$this->month.".xls");
        $this->createExcel();
        echo iconv("UTF-8", "GBK", $this->excel);
    }

    private function createExcel() {
        $arrRss = $this->getRss($this->year, $this->month);
        if (!$arrRss[0]) {
            $this->excel = $arrRss[1];
        } else {
            $this->excel = $this->createLine($this->arrTitle);
            $rss = $arrRss[1];
            foreach ($rss as $k => $rs) {
                $this->excel .= $this->createLine(array($rs['username'], $rs['name'], $rs['phone'], $rs['qq'], $rs['email'], date('Y-m-d H:i:s', $rs['reg_time'])));
            }
        }
    }

    private function createLine($arr) {
        $str = implode("\t", $arr)."\t\n";
        return $str;
    }

    private function getRss($year, $month) {
        if (!is_numeric($year) || !is_numeric($month))
            return array(false, '无效的日期');
        $cym = $year.$month;
        if (strlen($cym) != 6)
            return array(false, '字符长度不符合日期格式');
        $conn = smy\connect::GetPlatConn();
        $sql = 'SELECT username, email, qq, phone, name, reg_time FROM zde_members WHERE DATE_FORMAT(FROM_UNIXTIME(reg_time), "%Y%m") = "'.$cym.'" LIMIT 5000';
        $rss = $conn->querySql($sql);
        return array(true, $rss);
    }
}

?>
