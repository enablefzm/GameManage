<?php

class ob_res_zonefield extends ob_res_fieldbase {
    public function __construct() {
        $this->addField('zoneID', '分区ID');
        $this->addField('zoneName', '分区名称');
    }
}
?>
