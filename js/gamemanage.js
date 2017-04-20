// 页面加载完成时触发事件
window.onload = function() {
	// 初始化Gv
	Gv.init();
	// 获取管理平台信息
	Gm.send('systeminfo', function(jsondb){
		window.document.title = jsondb.DBs.AppName + ' ' + jsondb.DBs.AppVersion;
	});
	// 判断是否已经登入
	Gm.send('checkislogin', function(jsondb) {
		if (jsondb.DBs == true) {
			// 显示主界面
			Gv.showMain();
		} else {
			Gv.showLogin();
		}
	});
}

// GameManage类
var Gm;

(function(GM){
	GM.appName    = '';
	GM.appVersion = '';
	GM.serverPath = 'do.php';
	GM.send = function(cmd, backFunc) {
		$.ajax({
			'url': 		this.serverPath,
			'dataType': 'json',
			'data': 	{'cmd': cmd},
			'success': 	function(jsondb){
				GM._sendSuccess(jsondb, backFunc);
			}
		});
	};
	GM._sendSuccess = function(jsondb, backFunc) {
		console.log(jsondb);
		backFunc(jsondb);
	};
	GM.sendResult = function(result, backFunc) {

	};
	GM.getAppInfo = function(funcBack) {

	};
	GM.checkIsLogin = function(backFunc) {

	};
})(Gm || (Gm = {}));

var Gv;
(function(Gv) {
	Gv.init = function() {
		// 绑定退出
		$('#btnLoginOut').bind('click', function() {
			Gm.send('loginout', function(jsondb) {
				if (jsondb.RES == true) {
					Gv.showLogin();
				} else {
					console.log(jsondb.MSG);
				}
			});
		});
		// 绑定登陆
		$('#btnLogin').bind('click', function() {
			var uid = $.trim($('#txtLoginUid').val());
			var pwd = $.trim($('#txtLoginPwd').val());
			if (uid.length < 2 || pwd.length < 2) {
				Gv.showLoginInfo('请输入用户名和密码！');
				return;
			}
			var cmd = 'login ' + uid + ' ' + pwd;
			Gm.send(cmd, function(jsondb) {
				if (jsondb.RES != true) {
					Gv.showLoginInfo(jsondb.MSG);
				} else {
					Gv.showMain();
				}
			});
		});
	};
	Gv.showLogin = function() {
		$('#divLoginBox').show();
		$('#divMain').hide();
	};
	Gv.showMain = function() {
		$('#divMain').show();
		$('#divLoginBox').hide();
		// 获取操作员信息
	};
	// 显示登入界面下面的提示信息
	Gv.showLoginInfo = function(msg) {
		$('#divLoginInfo').show();
		$('#spLoginInfo').html(msg);
	};

})(Gv || (Gv = {}));
