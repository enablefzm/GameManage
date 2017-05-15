<?php
// 圣魔印游戏的Zone对象
namespace smy;

class zone extends \ob_zone implements \ob_inter_zone {
    private $zoneAttrib = array(
        'gameServerIP'   => '',
        'gameServerPort' => '',
    );

    public function __construct($rs) {
        parent::__construct($rs);
        $arrs = explode(',', $rs['zoneAttrib']);
        foreach($arrs as $k => $v) {
            $arr = explode('=', $v);
            if (count($arr) == 2) {
                switch($arr[0]) {
                    case 'gameServerIP':
                        $this->zoneAttrib['gameServerIP'] = $arr[1]; break;
                    case 'gameServerPort':
                        $this->zoneAttrib['gameServerPort'] = $arr[1]; break;
                }
            }
        }
    }

    public function getInfo() {
        return parent::getBaseInfo();
    }

    public function getGameServerIP() {
        return $this->zoneAttrib['gameServerIP'];
    }

    public function getGameServerPort() {
        return $this->zoneAttrib['gameServerPort'];
    }

    public function getSaveAttrib() {
        return sprintf('gameServerIP=%s,gameServerPort=%s', $this->getGameServerIP(), $this->getGameServerPort());
    }
}

?>
