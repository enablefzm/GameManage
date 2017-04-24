<?php

class ob_zone {
    private $attrib = array(
        'id' => 0,
        'gameID' => 0,
        'zoneID'   => 0,
        'zoneName' => 0,
        'zoneDate' => 0,
    );
    protected $id;
    protected $gameID;
    protected $zoneID;
    protected $zoneName;
    protected $zoneDate;

    public function __construct($rs) {
        foreach($rs as $k => $v) {
            $this->attrib[$k] = $v;
        }
        $this->id       = $this->attrib['id'];
        $this->gameID   = $this->attrib['gameID'];
        $this->zoneID   = $this->attrib['zoneID'];
        $this->zoneName = $this->attrib['zoneName'];
        $this->zoneDate = $this->attrib['zoneDate'];
    }

    public function getBaseInfo() {
        return array(
            'id' => $this->id,
            'gameID' => $this->gameID,
            'zoneID'   => $this->zoneID,
            'zoneName' => $this->zoneName,
            'zoneDate' => $this->zoneDate
        );
    }
}
?>
