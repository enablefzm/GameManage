<?php

class ob_res_countmons implements ob_res_inter {
    const K_MONTHS = 'K_MONTHS';
    const K_COUNT  = 'K_COUNT';
    private $res;
    public function __construct() {
        $this->res = array(
            self::K_MONTHS => new ob_res('充值月份列表'),
            self::K_COUNT  => new ob_res('充值月份合计')
        );
    }

    public function addMothsMenu($menu, $width = 0) {
        $this->res[self::K_MONTHS]->addMenu($menu, $width);
    }

    public function addMothsDb($db) {
        $this->res[self::K_MONTHS]->addDb($db);
    }

    public function addCountMenu($menu, $width = 0) {
        $this->res[self::K_COUNT]->addMenu($menu, $width);
    }

    public function addCountDb($db) {
        $this->res[self::K_COUNT]->addDb($db);
    }

    public function getRes() {
        return array(
            self::K_MONTHS => $this->res[self::K_MONTHS]->getRes(),
            self::K_COUNT => $this->res[self::K_COUNT]->getRes()
        );
    }
}

?>