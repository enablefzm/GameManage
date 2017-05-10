<?php
namespace smy;

class socket {
    private $sock;
    const XOR_KEY = 'JIMMY&Fzm';
    private $severID;

    /**
     * 构造对象
     * @param zone $obZone
     *  失败则会抛出异常
     */
    private function __construct($obZone) {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->sock) {
            throw new \Exception("无法创建Socket");
            return;
        }
        // 连接到指定对象
        $blnOK = socket_connect($this->sock, $obZone->getGameServerIP(), $obZone->getGameServerPort());
        if (!$blnOK) {
            $err = socket_last_error($this->sock);
            throw new \Exception('连接服务器失败：'.socket_strerror($err));
            return;
        }
        // 建立验证
        // $sendLink =
    }

    private function getSendLinkJson() {
        $arrSend = array(
            'serverType' => 13,
            // 'IP' =>
        );
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

    public static function send($json) {

    }
}

?>