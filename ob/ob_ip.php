<?php
// IP管理的基本属性
class ob_ip {
    protected $id;
    protected $ip;

    public function __construct($rs) {
        foreach ($rs as $k => $v) {
            switch ($k) {
                case 'id': $this->id = $v; break;
                case 'ip': $this->ip = $v; break;
            }
        }
    }

    public function getInfo() {
        return array(
            'id' => $this->id,
            'ip' => $this->ip
        );
    }
}

?>