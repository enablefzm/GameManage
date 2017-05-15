<?php
namespace smy;
require_once(__DIR__.'/zone.php');
require_once(__DIR__.'/socket.php');
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
        $res->addMenu('游戏服IP', 0);
        $res->addMenu('游戏服端口', 0);
        $res->addMenu('创建时间', 0);
        $rss = \ob_conn_connect::GetConn()->query('zones', 'gameID='.$this->getID());
        foreach ($rss as $k => $rs) {
            // $ob = \ob_gateway::newZone($this->getGameKey(), $rs);
            $ob = new zone($rs);
            $info = $ob->getInfo();
            $res->addDb(array(
                $info['id'],
                $info['zoneID'],
                $this->getName(),
                $info['zoneName'],
                $ob->getGameServerIP(),
                $ob->getGameServerPort(),
                date('Y-m-d', strtotime($info['zoneDate']))
            ));
        }
        return $res->getRes();
    }

    private function getObZone($zoneID) {
        $rs = \ob_zone::getZoneRs($zoneID);
        if (!$rs)
            return array(false, '分区不存在');
        $obZone = new zone($rs);
        if ($obZone->getGameID() != $this->getID())
            return array(false, '这个分区不属这个游戏');
        return array(true, $obZone);
    }

    private function readSocket($sock) {
        try {
            // 读取数据
            $readVal = $sock->read();
            $json = json_decode($readVal, true);
            return array(true, $json);
        } catch (\Exception $e) {
            return array(false, $e->getMessage());
        }
    }

    private function sendSocket($sock, $arrInfo) {
        try {
            $sock->send(json_encode($arrInfo, JSON_UNESCAPED_UNICODE));
            return array(true, '');
        } catch (\Exception $e) {
            return array(false, $e->getMessage());
        }
    }

    /**
     * 踢人下线
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_game::kickRole()
     */
    public function kickRole($zoneID, $roleName) {
        if (strlen($roleName) < 1)
            return array(false, '你要踢谁下线？');
        $arrResult = $this->getObZone($zoneID);
        if (!$arrResult[0])
            return $arrResult;
        $obZone = $arrResult[1];
        // 获得连接对象
        $sock = socket::newSocket($obZone);
        $kickProtec = array(
            'type' => 11,
            'serverId' => (int)$zoneID,
            'name'     => $roleName
        );
        // $sock->send(json_encode($kickProtec, JSON_UNESCAPED_UNICODE));
        $arrSend = $this->sendSocket($sock, $kickProtec);
        if (!$arrSend[0])
            return $arrSend;
        // 读取数据
        $arrResult = $this->readSocket($sock);
        if ($arrResult[0]) {
            $json = $arrResult[1];
            if ($json['result'] == 1) {
                return array(true, '踢人下线成功！');
            } else {
                return array(false, '踢人下线失败！');
            }
        } else {
            return $arrResult;
        }
    }

    /**
     * 查看指定帐号的角色信息
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_game::seeRoles()
     */
    public function seeRoles($zoneID, $uid) {

        return array(false, '没有这个玩家的角色信息');
    }

    /**
     * 执行添加分区操作
     * @param string $strArgs
     * @return int
     *       1 操作成功
     *      -1 缺少参数
     *      -2 zoneID已存在
     */
    public function addZone($strArgs) {
        $arrSaveField = self::operateArgs($strArgs);
        if (!$arrSaveField) {
            return -1;
        }
        $rss = \ob_conn_connect::GetConn()->query('zones', 'gameID = '.$this->getID().' AND zoneID='.$arrSaveField['zoneID']);
        if (count($rss) > 0) {
            return -2;
        }
        $saveInfo = array(
            'gameID'   => $this->getID(),
            'zoneID'   => $arrSaveField['zoneID'],
            'zoneName' => $arrSaveField['zoneName'],
            'zoneAttrib' => sprintf('gameServerIP=%s,gameServerPort=%s', $arrSaveField['gameIP'], $arrSaveField['gamePort'])
        );
        $obZone = new zone($saveInfo);
        $obZone->save();
        return 1;
    }

    public static function getZoneFields() {
        $obField = new \ob_res_zonefield();
        $obField->addField('gameIP', '游戏服IP');
        $obField->addField('gamePort', '游戏服端口');
        return $obField;
    }

    private static function operateArgs($strArgs) {
        // 分解参数
        $arrs = explode(',', $strArgs);
        $saveField = array();
        foreach ($arrs as $k => $v) {
            $arr = explode('=', $v);
            if (count($arr) == 2) {
                $saveKey = $arr[0];
                $saveVal = $arr[1];
                if (strlen($saveVal) < 1)
                    return false;
                switch ($saveKey) {
                    case 'zoneID':
                        $saveVal = floor($saveVal);
                        if ($saveVal < 0)
                            return false;
                        $saveField[$saveKey] = $saveVal;
                        break;
                    case 'gameIP':
                        if (!\ob_feature::isIpAdder($saveVal))
                            return false;
                        $saveField[$saveKey] = $saveVal;
                        break;
                    case 'gamePort':
                        if (!is_numeric($saveVal))
                            return false;
                        $saveField[$saveKey] = $saveVal;
                        break;
                    case 'zoneName':
                        $saveField[$saveKey] = $saveVal;
                        break;
                }
            }
        }
        if (count($saveField) != 4) {
            return false;
        }
        return $saveField;
    }
}
?>
