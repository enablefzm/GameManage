<?php
namespace smy;
require_once(__DIR__.'/Byte.php');
require_once(__DIR__.'/config.php');

class socket {
    const XOR_KEY = 'Yhyx$SMY2017';
    private static $obSock;

    private $sock;
    private $serverID;
    private $host;
    private $port;
    private $linkKey = 'XmYhyx$SMY2017';

    /**
     * 构造对象
     * @param zone $obZone
     *  失败则会抛出异常
     */
    public function __construct($host, $port, $serverID) {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->sock) {
            throw new \Exception("无法创建Socket");
            return;
        }
        socket_setopt($this->sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));
        socket_setopt($this->sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 3, 'usec' => 0));
        // 连接到指定对象
        $this->host = $host;
        $this->port = $port;
        $this->serverID = $serverID;
        $blnOK = @ socket_connect($this->sock, $this->host, $this->port);
        if (!$blnOK) {
            $err = socket_last_error($this->sock);
            throw new \Exception('连接服务器失败。');
            return;
        }
        // 建立验证
        $linkProtec = $this->getSendLinkJson();
        $cryProtec  = self::xorEnc($linkProtec, self::XOR_KEY);
        $this->send($cryProtec);
    }

    public function send($data) {
        $byte = new Byte($data);
        $result = socket_write($this->sock, $byte->getByte());
        if (!$result) {
            $err = socket_last_error($this->sock);
            throw new \Exception('发送消息失败：'.socket_strerror($err));
        }
    }

    public function read() {
        $buf = @ socket_read($this->sock, 2048, PHP_NORMAL_READ);
        if (!$buf) {
            $err = socket_last_error($this->sock);
            throw new \Exception('读取数据失败'.$err);
        }
        \ob_log::logAct('SOCK_READ', $buf);
        return $buf;
    }

    public function __destruct() {
        if ($this->sock)
            socket_close($this->sock);
    }

    private function getSendLinkJson() {
        $ip = $this->getLocalIP();
        $arrSend = array(
            'serverType' => 13,
            'IP' => $ip,
            'serverID' => (int)$this->serverID,
        );
        $arrSend['sign'] = md5($arrSend['serverType'].$ip.$this->serverID.$this->linkKey);
        return json_encode($arrSend, JSON_UNESCAPED_UNICODE);
    }

    private function getLocalIP() {
        socket_getsockname($this->sock, $ip);
        return $ip;
    }

    /**
     * 异或加密
     * @param string $str
     * @param string $key
     * @return string
     */
    public static function xorEnc($str, $key) {
        $cryTxt = '';
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($str); $i++) {
            $k = $i % $keyLen;
            $cryTxt .= $str[$i] ^ $key[$k];
        }
        return $cryTxt;
    }

    /**
     * 创建Socket对象
     * @param zone $obZone
     * @return socket
     */
    public static function newSocket($obZone) {
        if (!self::$obSock) {
            try {
                self::$obSock = new socket(config::GAME_IP, config::GAME_PORT, $obZone->getZoneID());
            } catch (\Exception $e) {
                echo \ob_conn_res::CreateSystemError('连接游戏服务器出错')->ToJson();
                die(0);
            }
        }
        return self::$obSock;
    }
}

?>
