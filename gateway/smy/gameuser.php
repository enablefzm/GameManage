<?php
namespace smy;
require_once(__DIR__.'/connect.php');

class gameuser implements \ob_inter_gameuser {
    static private $tlbName = 'zde_members';

    private $uid;
    private $lastlogintime;
    private $username;
    private $email;
    private $regtime;
    private $qq;
    private $name;
    private $phone;
    private $idCard;
    private $forbidden;

    public function __construct($rs) {
        $this->uid           = $rs['uid'];
        $this->lastlogintime = $rs['lastlogintime'];
        $this->username      = $rs['username'];
        $this->email         = $rs['email'];
        $this->regtime       = $rs['reg_time'];
        $this->qq            = $rs['qq'];
        $this->name          = $rs['name'];
        $this->phone         = $rs['phone'];
        $this->idCard        = $rs['id_card'];
        $this->forbidden     = $rs['forbidden'];
    }

    /**
     * 获得玩家具体信息
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getUserInfo()
     */
    public function getUserInfo() {
        $obRes = new \ob_gameuserres($this->uid);
        $obRes->addFunc(\ob_gameuserres::FUN_EDIT_PASS);
        $obRes->addFunc(\ob_gameuserres::FUN_FORBIDDEN);
        $obRes->addDb(\ob_gameuserres::TEXT, '系统ID', $this->uid);
        $obRes->addDb(\ob_gameuserres::TEXT, '帐号UID', $this->username);
        $obRes->addDb(\ob_gameuserres::TEXT, '姓名', $this->name);
        $obRes->addDb(\ob_gameuserres::TEXT, '手机号', $this->phone);
        $obRes->addDb(\ob_gameuserres::TEXT, 'QQ', $this->qq);
        $obRes->addDb(\ob_gameuserres::TEXT, '身份证号', $this->idCard);
        $obRes->addDb(\ob_gameuserres::TEXT, 'Email', $this->email);
        $obRes->addDb(\ob_gameuserres::TEXT, '最后一次登入', date('Y-m-d H:i:s', $this->lastlogintime));
        $obRes->addDb(\ob_gameuserres::TEXT, '注册时间', date('Y-m-d H:i:s', $this->regtime));
        $obRes->addDb(\ob_gameuserres::TEXT, '是否被封号', ($this->forbidden > 0) ? '被封号' : '未被封号');
        return $obRes;
    }

    /**
     * 修改玩家的新密码
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::updatePassword()
     */
    public function updatePassword($newpwd) {
        // 获取UC中心
        $ucConn = connect::GetUcConn();
        // 获取UC数据对象
        $ucRss = $ucConn->query('uc_members', 'username="'.$this->username.'"');
        $rs    = $ucRss[0];
        if (count($ucRss) != 1) {
            die(\ob_conn_res::CreateSystemError("查询UC玩家信息出错")->ToJson());
            return false;
        }
        $md5Val = md5($newpwd);
        // 获取UC的salt
        $salt = $rs['salt'];
        // 更新UC的密码
        $ucpwd = md5($md5Val.$salt);
        $resCount = $ucConn->updata('uc_members', 'username="'.$this->username.'"', array('password' => $ucpwd));
        if ($resCount < 0) {
            die(\ob_conn_res::CreateSystemError("更新UC平台玩家密码出错 " . $resCount)->ToJson());
            return false;
        }
        // 更新平台的密码
        $res = connect::GetPlatConn()->updata(self::$tlbName, 'uid='.$this->uid, array('password' => $md5Val));
        if ($res < 0) {
            die(\ob_conn_res::CreateSystemError("更新平台的玩家密码信息出错")->ToJson());
            return false;
        } else {
            return true;
        }

    }

    static private function getPages($max, $page) {
        $pages = array();
        if ($max > 12) {
            $minPage = $page - 6;
            $maxPage = $page + 6;
            for (; $minPage < 1; $minPage++) {
                $maxPage ++;
            }
            for (; $maxPage > $max; $maxPage--) {
                $minPage --;
            }
            for ($i = $minPage; $i <= $maxPage; $i++) {
                $pages[] = $i;
            }
        } else {
            for ($i = 1; $i <= $max; $i++) {
                $pages[] = $i;
            }
        }
        return $pages;
    }

    /**
     * 查询玩家帐号列表
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListUserResDb()
     */
    static public function getListUserResDb($page, $search) {
        $res = new \ob_res('玩家帐号列表');
        $res->addMenu('系统ID', 0);
        $res->addMenu('帐号UID', 0);
        $res->addMenu('姓名', 0);
        $res->addMenu('Email', 0);
        $res->addMenu('手机号', 0);
        $res->addMenu('注册时间', 0);
        $keys = null;
        if ($search) {
            $arrSearch = explode('=', $search);
            if (count($arrSearch) == 2) {
                switch ($arrSearch[0]) {
                    case 'uid':
                        $keys = 'username LIKE "%'.$arrSearch[1].'%"';
                        break;
                    case 'name':
                        $keys = 'name LIKE "%'.$arrSearch[1].'%"';
                        break;
                }
            }
        }
        $rss = connect::GetPlatConn()->query(self::$tlbName, $keys, $page);
        foreach ($rss as $k => $rs) {
            $res->addDb(array($rs['uid'], $rs['username'], $rs['name'], $rs['email'], $rs['phone'], date('Y-m-d', $rs['reg_time'])));
        }
        $max = ceil(connect::GetPlatConn()->count(self::$tlbName, $keys) / 30);
        $pages = self::getPages($max, $page);
        $res->setPage($max, $pages, $page);
        return $res;
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_inter_gameuser::getListSearchVal()
     */
    static public function getListSearchVal() {
        return array(
            'uid' => '玩家帐号',
            // 'name' => '玩家姓名'
        );
    }

    /**
     * 创建一个玩家帐号实例
     * !CodeTemplates.overridecomment.nonjd!
     * @see \ob_inter_gameuser::newGameUser
     */
    static public function newGameUser($guid) {
        $guid = floor($guid);
        // 具体去获取相应的玩家对象
        // return new gameuser();
        $rss = connect::GetPlatConn()->query(self::$tlbName, 'uid='.$guid);
        if (count($rss) != 1) {
            return null;
        }
        return new gameuser($rss[0]);
    }
}

?>