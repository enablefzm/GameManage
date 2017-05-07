<?php
// 游戏服务器对象
class ob_game {
    private $attribs = array(
        'id'       => 0,
        'gameName' => '',
        'gameKey'  => '',
        'gameDate'     => 0
    );
    // 构造对象
    //  @parames
    //      array $rs 数据库里读取出来的数据
    public function __construct($rs) {
        foreach ($rs as $k => $v) {
            $this->attribs[$k] = $v;
        }
    }

    public function getAttrib() {
        return $this->attribs;
    }

    public function getName() {
        return $this->attribs['gameName'];
    }

    public function  getID() {
        return $this->attribs['id'];
    }

    public function getGameKey() {
        return $this->attribs['gameKey'];
    }

    public function getInfo() {
        return array(
            'id' => $this->attribs['id'],
            'name' => $this->attribs['gameName'],
            'key'  => $this->attribs['gameKey']
        );
    }

    public function getZone($zoneID) {
        return ob_zone::getZoneInZoneID($this->getID(), $zoneID);
    }

    // 通过ID获得指定的某个对象
    //  @parames
    //      int $id 游戏ID
    //  @return
    //      ob_games || null
    static public function getGame($id) {
        $rss = ob_conn_connect::GetConn()->query('games', 'id='.$id);
        if (count($rss) != 1) {
            return null;
        }
        return new ob_game($rss[0]);
    }

    // 获得所有的游戏对象
    //  @return
    //      array 包含ob_games对象的数组
    static public function getGames() {
        $rss = ob_conn_connect::GetConn()->query('games');
        $obs = array();
        for ($i = 0; $i < count($rss); $i++) {
            $obs[] = new ob_game($rss[$i]);
        }
        return $obs;
    }
}
?>
