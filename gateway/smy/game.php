<?php
namespace smy;

/**
 * 游戏名称：圣魔印
 * 游戏KEY： smy
 * @author Andy
 *
 */
class game extends \ob_game implements \ob_inter_game {
    public function __construct($rs) {
        parent::__construct($rs);
    }

    public function getListZoneResDb() {
        $res = new \ob_res('分区列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('分区ID', 0);
        $res->addMenu('游戏名称', 0);
        $res->addMenu('分区名称', 0);
        $res->addMenu('创建时间', 0);
        $rss = \ob_conn_connect::GetConn()->query('zones', 'gameID='.$this->getID());
        foreach ($rss as $k => $rs) {
            $ob = \ob_gateway::newZone($this->getGameKey(), $rs);
            $info = $ob->getInfo();
            $res->addDb(array(
                $info['id'],
                $info['zoneID'],
                $this->getName(),
                $info['zoneName'],
                date('Y-m-d', strtotime($info['zoneDate']))
            ));
        }
        return $res->getRes();
    }

    public static function getZoneFields() {
        $obField = new \ob_res_zonefield();
        $obField->addField('gameIP', '游戏服IP');
        $obField->addField('gamePort', '游戏服端口');
        return $obField;
    }

}

?>
