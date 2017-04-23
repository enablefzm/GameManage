// 页面加载完成时触发事件
window.onload = function() {
	// 初始化Gv
	Gv.init();
	// 获取管理平台信息
	Gm.send('systeminfo', function(jsondb){
		Gm.appName = jsondb.DBs.AppName;
		Gm.appVersion = jsondb.DBs.AppVersion;
		window.document.title = Gm.appName;
		// 判断是否已经登入
		Gm.send('checkislogin', function(jsondb) {
			if (jsondb.DBs == true) {
				// 显示主界面
				Gv.showMain();
			} else {
				Gv.showLogin();
			}
		});
	});
}

// GameManage类
var Gm;
(function(GM){
	GM.appName    = '';
	GM.appVersion = '';
	GM.serverPath = 'do.php';
	Gm.gameName   = '未选定';
	Gm.gameZone   = '';

	// 玩家信息数据
	GM.DBs = {
		'User': {
			'id': 0,
			'uid': '',
			'name': ''
		}
	};
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

// GameManageView类
var Gv;
(function(Gv) {
	// 初始化View类
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
	// 显示登入界面
	Gv.showLogin = function() {
		$('#divLoginBox').show();
		$('#divMain').hide();
		$('#txtLoginUid').val('');
		$('#txtLoginPwd').val('');
		$('#divLoginInfo').hide();
	};
	// 显示主界面
	Gv.showMain = function() {
		$('#divMain').show();
		$('#divLoginBox').hide();
		// 显示游戏信息
		$('#spMainAppName').text(Gm.appName);
		$('#spMainAppVer').text('Ver' + Gm.appVersion);
		// 获取操作员信息
		Gm.send('user info', function(jsondb) {
			if (!jsondb.RES)
				return;
			Gm.DBs.User.id = jsondb.DBs.id;
			Gm.DBs.User.uid = jsondb.DBs.uid;
			Gm.DBs.User.name = jsondb.DBs.name;
			$('#spMainUserName').text(Gm.DBs.User.name);
		});
	};
	// 显示登入界面下面的提示信息
	Gv.showLoginInfo = function(msg) {
		$('#divLoginInfo').show();
		$('#spLoginInfo').html(msg);
	};
	// 显示中区管理
	Gv.Content = {
		'nowContent': null,
		'arrContent': {}
	};
	// 显示不同区的内容
	Gv.Content.showContent = function(cType) {
		if (!this.arrContent[cType]) {
			return;
		}
		if (this.nowContent)
			this.nowContent.hide();
		this.arrContent[cType].show();
		this.nowContent = this.arrContent[cType];
	};
	Gv.Content.regContent = function(cType, ob) {
		this.arrContent[cType] = ob;
	};
	// 显示指定的节点内容
	// 	@parames
	// 		dTitle  标题节点名称
	// 		dHead   菜单列表节点名称
	// 		dBody   正文节点名称
	// 		md 		要生成内容的主节点
	// 		showDb	要在指定节点生成的内容数据
	// 			{
	// 				title: '' 标题
	// 				menus: [['名称', 宽度]...] 菜单
	// 				dbs:   [数据列]
	// 				key:   查询的主键
	// 			}
	Gv.Content.createTable = function(dTitle, dHead, dBody, showDb, actionDb) {
		dTitle.text(showDb.title);
		dHead.empty();
		dBody.empty();
		// 创建标题
        var sHead = "<tr>";
        for (var k in showDb.menus) {
        	var t = showDb.menus[k];
        	if (t[1] > 0) {
        		sHead += '<th style="width:' + t[1] + 'px;">' + t[0] + '</th>';
        	} else {
        		sHead += '<th>' + t[0] + '</th>';
        	}
        }
        if (actionDb) {
        	sHead += '<th style="width:200px;">操作</th>'
        }
        sHead += "</tr>";
		dHead.append($(sHead));
		// 添加正文
		var dbs = showDb.dbs;
		for (var k in dbs) {
			var arr = dbs[k];
			var s = '<tr>';
			for (var t in arr) {
				s += '<td>' + arr[t] + '</td>';
			}
			s += '</tr>';
			var tr = $(s);
			if (actionDb) {
				var st = $('<td style=""></td>');
				for (var i = 0; i < actionDb.length; i++) {
					if (i > 0) {
						st.append($('<span>&nbsp;&nbsp;</span>'));
					}
					var func = actionDb[i][1];
					var arg  = arr[showDb.key];
					sa = $('<a href="javascript:void(0);">' + actionDb[i][0] + '</a>');
					sa[0].doFunc = func;
					sa[0].doArg  = arg;
					sa.click(function(e){ this.doFunc(this.doArg); });
					st.append(sa);
				}
				tr.append(st);
			}
			dBody.append(tr);
		}
	};
	// 清除列表信息
	Gv.Content.clearTableList = function(md) {

	};

})(Gv || (Gv = {}));

// 游戏列表窗口对象
(function() {
	var WinContent = {
		'mName' : 'JIMMY',
		'show': function() {
			$('#contGameList').show();
			Gm.send('game list', function(jsondb) {
				WinContent.showDb(jsondb);
			});
		},
		'hide': function() {
			$('#contGameList').hide();
		},
		'showDb': function(jsondb) {
			Gv.Content.createTable($('#contGameListTitle'), $('#contGameListHead'), $('#contGameListBody'), jsondb.DBs, [
				['查看', function(gid) {
					WinContent.showGameInfo(gid);
				}]
			]);
		},
		showGameInfo: function(gId) {
			console.log("要查看：", this.mName, " gId:", gId);
		}
	};
	Gv.Content.regContent('gameList', WinContent);
}());
