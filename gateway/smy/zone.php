<?php
// 圣魔印游戏的Zone对象
namespace smy;

class zone extends \ob_zone implements \ob_inter_zone {
    private $zoneAttrib = array();

    public function __construct($rs) {
        parent::__construct($rs);
    }

    public function getInfo() {
        return parent::getBaseInfo();
    }
}

?>
