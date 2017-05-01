<?php
namespace smy;

class game extends \ob_game implements \ob_inter_game {
    private $name = '圣魔印';

    public function __construct($rs) {
        parent::__construct($rs);
    }

    public function getListZoneResDb() {
        $res = new \ob_res('分区列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('分区ID', 0);
        $res->addMenu('游戏名称', 0);
        $res->addMenu('分区名称', 0);
        $res->addMenu('游戏区状态', 0);
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
                '在线',
                date('Y-m-d', strtotime($info['zoneDate']))
            ));
        }
        return $res->getRes();
    }
}

?>
