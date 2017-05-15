<?php

class ob_zone {
    const TLB_NAME = 'zones';

    private $attrib = array(
        'id' => 0,
        'gameID' => 0,
        'zoneID'   => 0,
        'zoneName' => 0,
        'zoneDate' => 0,
    );
    protected $id = 0;
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

    public function getID() {
        return $this->id;
    }

    public function getZoneID() {
        return $this->zoneID;
    }

    public function getZoneName() {
        return $this->zoneName;
    }

    public function getGameID() {
        return $this->gameID;
    }

    public function getSaveAttrib() {
        return '';
    }

    public function save() {
        $saveInfo = array(
            'gameID' => $this->gameID,
            'zoneID' => $this->zoneID,
            'zoneName' => $this->zoneName,
            'zoneAttrib' => $this->getSaveAttrib()
        );
        if ($this->id > 0) {
            ob_conn_connect::GetConn()->updata(self::TLB_NAME, 'id='.$this->id, $saveInfo);
        } else {
            $lastID = ob_conn_connect::GetConn()->updata(self::TLB_NAME, null, $saveInfo, true);
            $this->id = $lastID;
        }
    }

    static public function getZone($id) {
        $rs = self::getZoneRs($id);
        if (!$rs)
            return null;
        return new ob_zone($rs);
    }

    static public function getZoneRs($id) {
        $id = floor($id);
        $rss = ob_conn_connect::GetConn()->query("zones", "id=".$id);
        if (count($rss) != 1)
            return null;
        return $rss[0];
    }

    static public function getZoneInZoneID($gameID, $zoneID) {
        $rss = ob_conn_connect::GetConn()->query('zones', 'gameID='.$gameID.' AND zoneID='.$zoneID);
        if (count($rss) != 1)
            return null;
        return new ob_zone($rss[0]);
    }
}
?>
