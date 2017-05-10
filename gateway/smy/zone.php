<?php
// 圣魔印游戏的Zone对象
namespace smy;

class zone extends \ob_zone implements \ob_inter_zone {
    private $zoneAttrib = array(
        'gameServerIP'   => '',
        'gameServerPort' => '',
        'gameSignKey'    => '',
        'gameLinkKey'    => ''
    );

    public function __construct($rs) {
        parent::__construct($rs);
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

    public function getGameKey() {
        return $this->zoneAttrib['gameKey'];
    }
}

?>
