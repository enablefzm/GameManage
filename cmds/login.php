<?php
// 登入命令
class login implements ob_ifcmd {

    public function doCmd($cmd, $args) {
        if (count($args) != 2) {
            return ob_conn_res::GetResAndSet($cmd, false, '参数不够');
        }
        $uid = $args[0];
        $pwd = $args[1];
        // 构造用户数据
        $obUser = ob_user::GetUserInUid($uid);
        if (!$obUser) {
            return ob_conn_res::GetResAndSet($cmd, false, '用户不存在');
        }
        // 判断密码
        $bln = $obUser->chekcPass($pwd);
        if (!$bln) {
            return ob_conn_res::GetResAndSet($cmd, false, "密码不存确");
        }
        // 执行登入操作
        $obSess = ob_session::GetSess();
        $obSess->setUid($uid);
        // 执行缺省只有一个游戏时选择它
        $this->selectDefaultGame();
        return ob_conn_res::GetResAndSet($cmd, true, '登入成功');
    }

    // 如果游戏服只有一个则默认选择它
    private function selectDefaultGame() {
        if (ob_session::GetSess()->getGameID())
            return;
        $games = ob_game::getGames();
        if (count($games) == 1) {
            $obGame = $games[0];
            ob_session::GetSess()->setGameID($obGame->getID());
        }
    }
}
?>
