<?php
// 消息返回格式
class ob_res {
    private $title   = '';
    private $menus   = array();
    private $dbs     = array();
    private $key     = 0;
    private $func    = array();     // 其它调用名称关键字，命令[名称, cmd, key]
    private $navpage = array(
        'max'     => 0,
        'pages'   => array(),
        'nowpage' => 0
    );

    public function __construct($title) {
        $this->title = $title;
    }
    public function addMenu($menu, $width) {
        $this->menus[] = array($menu, $width);
    }
    public function addFunc($name, $cmd, $key) {
        $this->func[] = array($name, $cmd, $key);
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
    public function setPage($max, $pages, $nowPage) {
        $this->navpage['max']     = $max;
        $this->navpage['pages']   = $pages;
        $this->navpage['nowpage'] = $nowPage;
    }
    public function setTitle($title) {
        $this->title = $title;
    }
    public function getRes() {
        return array(
            'title'   => $this->title,
            'menus'   => $this->menus,
            'dbs'     => $this->dbs,
            'key'     => $this->key,
            'navpage' => $this->navpage,
            'func'    => $this->func
        );
    }
}
?>
