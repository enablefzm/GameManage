<?php
// 玩家信息返回格式对象
class ob_gameuserres {
    const TEXT          = 'TEXT';
    const TEXT_RED      = 'TEXT_RED';
    const FUN_EDIT_PASS = 'F_EDIT_PASS';
    const FUN_FORBIDDEN = 'F_FORBIDDEN';
    const FUN_SEE_ROLE  = 'F_SEE_ROLE';

    private $dbs   = array();
    private $func  = array();
    private $key;

    public function __construct($key) {
        $this->key = $key;
    }

    public function addDb($strType, $strTitle, $strValue) {
        $this->dbs[$strTitle] = array($strType, $strValue);
    }

    public function addFunc($funcName) {
        $this->func[] = $funcName;
    }

    public function getRes() {
        return array(
            'key' => $this->key,
            'dbs' => $this->dbs,
            'func' => $this->func
        );
    }
}
?>
