<?php
namespace smy;
require_once(__DIR__.'/connect.php');

class ipforbidden extends \ob_ip implements \ob_inter_ip {
    const TLB_NAME = 'ip_forbidden';

    /**
     * 获取IP黑名单列表
     * @see \ob_inter_ip
     */
    static public function getList($page, $searchs) {
        $keys = null;
        $page = floor($page);
        if ($page < 1)
            $page = 1;
        // 如果要查询条件可以放在这里
        //  TODO..
        $rss = connect::GetPlatConn()->query(self::TLB_NAME, $keys, $page);
        $obRes = new \ob_res('IP黑名单列表');
        $obRes->addMenu('系统ID', 0);
        $obRes->addMenu('IP地址', 0);
        for ($i = 0; $i < count($rss); $i++) {
            $rs = $rss[$i];
            $obRes->addDb(array($rs['id'], $rs['ip']));
        }
        return $obRes;
    }

    static public function newIpForbidden($id) {
        return new ipforbidden(array());
    }

    /**
     * 获取可以添加的属性
     * @see \ob_inter_ip
     */
    static public function getAddField() {
        $obRes = new \ob_res_ipfield();
        $obRes->addField('ip', 'IP地址');
        return $obRes;
    }

    /**
     * 删除指定的IP地址
     * @param int $ipid
     */
    static public function delete($ipid) {
        $ipid = floor($ipid);
        $result = connect::GetPlatConn()->delete(self::TLB_NAME, 'id='.$ipid);
        if ($result < 0)
            return false;
        else
            return true;
    }
}

?>