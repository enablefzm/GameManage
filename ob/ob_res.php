<?php
// 消息返回格式
class ob_res {
    private $title = '';
    private $menus = array();
    private $dbs   = array();
    private $key   = 0;

    public function __construct($title) {
        $this->title = $title;
    }
    public function addMenu($menu, $width) {
        $this->menus[] = array($menu, $width);
    }
    public function setKey($idx) {
        $this->key = $idx;
    }
    public function addDb($db) {
        $this->dbs[] = $db;
    }
    public function setDbs($dbs) {
        $this->dbs = $dbs;
    }
    public function getRes() {
        return array(
            'title' => $this->title,
            'menus' => $this->menus,
            'dbs'   => $this->dbs,
            'key'   => $this->key
        );
    }
}

?>