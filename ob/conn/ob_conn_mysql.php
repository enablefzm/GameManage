<?php

class ob_conn_mysql {
    private $conn;
    private $cfg = array(
        'DBName' => '',
        'DBAddr' => '',
        'DBPort' => 3316,
        'DBUser' => '',
        'DBPass' => ''
    );

    public function __construct(Array $cfg = null) {
        // if (!$cfg) {
        //     $arrCfg = ob_fet_tools::operateStrToArr(MYSQL_CFG);
        //     $cfg = array(
        //         'DBName' => $arrCfg['DBName'],
        //         'DBAddr' => $arrCfg['DBIP'],
        //         'DBPort' => $arrCfg['DBPort'],
        //         'DBUser' => $arrCfg['DBUser'],
        //         'DBPass' => $arrCfg['DBPass']
        //     );
        // }
        // ob_fet_tools::fullArray($this->cfg, $cfg);
        $this->cfg = $cfg;
        $this->link();
    }

    /**
     *  连接服务器
     */
    private function link() {
        if ($this->conn == null) {
            try {
                // $arrSet = array(PDO::ATTR_PERSISTENT => true);
                $arrSet = array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8';");
                $this->conn = new PDO(sprintf("mysql:host=%s;port=%s;dbname=%s", $this->cfg['DBAddr'], $this->cfg['DBPort'], $this->cfg['DBName']),
                    $this->cfg['DBUser'],
                    $this->cfg['DBPass'],
                    $arrSet);
            }catch (Exception $ex) {
                die(var_dump($ex));
            }
        }
    }

    // 将数组转换成sql
    private function setParents($arrs) {
        if (!is_array($arrs))
            return false;

        $tmp = false;
        $str = "";
        foreach($arrs as $key => $value) {
            $type_info = gettype($value);
            switch($type_info) {
                case "integer" :
                    $str_temp = sprintf("%s=%s", $key ,$value);
                    break;
                default :
                    $str_temp = sprintf("%s='%s'", $key, $value);
                    break;
            }
            if (!$tmp) {
                $str = $str_temp;
                $tmp = true;
            } else {
                $str .= "," . $str_temp;
            }
        }
        return $str;
    }

    /**
     * 执行SQL语句
     * @param unknown $sql
     * @return 返回执行被影响的行数
     */
    private function execute($sql) {
        try {
            $count = $this->conn->exec($sql);
        } catch (PDOException $e) {
            // $this->write_connect_log($e->getMessage());
            // echo $e->getMessage().'<br />';
            return -1;
        }
        return $count;
    }

    /**
     * 删除表中某个记录
     * @param string $table
     * @param string $key
     * @return int 成功返回0以上数值
     */
    public function delete($table, $key) {
        if (!$key || strlen($key) < 3)
            return -1;
        $sql = 'DELETE FROM '.$table.' WHERE '.$key;
        return $this->execute($sql);
    }

    /**
     * 通对象类型获取数据
     * !CodeTemplates.overridecomment.nonjd!
     * @see ob_fet_conn_inte::query()
     */
    public function query($table, $key = null, $page = null, $limit = 30) {
        $sql = 'SELECT * FROM '.$table;
        if ($key) {
            $sql .= ' WHERE '. $key;
        }
        if ($page) {
            // limit
            $p = $page - 1;
            if ($p < 1) $p = 0;
            $start = $p * $limit;
            $sql .= ' LIMIT '.$start.','.$limit;
        }
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($table, $key = null) {
        $sql = 'SELECT COUNT(*) as count FROM '.$table;
        if ($key) {
            $sql .= ' WHERE '.$key;
        }
        $rss = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $count = $rss[0]['count'];
        return $count;
    }

    /**
     *  更新数据
     */
    public function updata($table, $key, $saveInfo, $isNew = false) {
        $strVal = $this->setParents($saveInfo);
        if (!$strVal)
            return 0;
        if ($isNew) {
            $strSql = sprintf('INSERT INTO %s SET %s', $table, $strVal);
            $count = $this->execute($strSql);
            if ($count > 0) {
                if ($this->conn->lastInsertId() > 0)
                    return $this->conn->lastInsertId();
            }
            return $count;
        } else {
            $strSql = sprintf('UPDATE %s SET %s WHERE %s', $table, $strVal, $key);
            $count  = $this->execute($strSql);
            return $count;
        }
    }

}

?>
