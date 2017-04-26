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
		},
		'SelectGame': {
			'game': null,
			'zone': null
		}
	};
	GM.OBs = {
		ArrOB: {},
		regOB: function(obName, ob) {
			this.ArrOB[obName] = ob;
		},
		getOB: function(obName) {
			return this.ArrOB[obName];
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
			// 显示已选中的游戏
			var tSelectGame = jsondb.DBs.SelectGameInfo;
			if (tSelectGame) {
				Gv.showSelectGameAndZone(tSelectGame[0], tSelectGame[1]);
			}
			// 执行显示游戏列表
			if (tSelectGame[0] && tSelectGame[0].length > 0) {
				Gv.Content.showContent('zoneList');
			} else {
				Gv.Content.showContent('gameList');
			}
		});
	};
	// 显示登入界面下面的提示信息
	Gv.showLoginInfo = function(msg) {
		$('#divLoginInfo').show();
		$('#spLoginInfo').html(msg);
	};
	Gv.showSelectGameAndZone = function(gameName, zoneName) {
		Gm.DBs.SelectGame.game = gameName;
		Gm.DBs.SelectGame.zone = zoneName;
		var tName = "未选定";
		if (gameName && gameName.length > 0) {
			tName = gameName;
			if (zoneName && zoneName.length > 0) {
				tName += " -" + zoneName;
			}
		}
		$('#spMainGameName').text(tName);
	};
	// 显示中区管理
	Gv.Content = {
		'nowContent': null,
		'arrContent': {},
		// 显示不同区的内容
		'showContent': function(cType, args) {
			if (!this.arrContent[cType]) {
				return;
			}
			if (this.nowContent)
				this.nowContent.hide();
			this.arrContent[cType].show(args);
			this.nowContent = this.arrContent[cType];
		},
		// 注册正文内空对象
		'regContent': function(cType, ob) {
			this.arrContent[cType] = ob;
		},
		// 显示指定的节点内容
		// 	@parames
		// 		dTitle  标题节点名称
		// 		dHead   菜单列表节点名称
		// 		dBody   正文节点名称
		// 		showDb	要在指定节点生成的内容数据
		// 			{
		// 				title: '' 标题
		// 				menus: [['名称', 宽度]...] 菜单
		// 				dbs:   [数据列]
		// 				key:   查询的主键
		// 			}
		//		actionDb 要生成的动作函数
		'createTable': function(dTitle, dHead, dBody, showDb, actionDb) {
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
	        	var wh = '150px';
	        	if (actionDb.length > 2) {
	        		wh = '200px';
	        	}
	        	sHead += '<th style="width:' + wh + '">操作</th>'
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
		},

		'showTable': function(showTableDb, actionDb) {
			this.createTable($('#contGameListTitle'), $('#contGameListHead'), $('#contGameListBody'), showTableDb, actionDb);
		},
		// 添加查找对象
		'addSearch': function() {

		}
	};
	// 显示提示信息
	Gv.DialogMsg = {
		// 显示带有勾选的按钮
		'showOkMsg': function(msg) {
			$('#alertModalTitle').text("提示");
			// $('#alertModalIcon').attr("class", "glyphicon glyphicon-ok-circle");
			$('#alertModalBody').css('color', "	#000000");
			this.showMsg(msg);
		},
		'showErrMsg': function(msg) {
			$('#alertModalTitle').text("错误");
			$('#alertModalBody').css('color', "#B22222");
			this.showMsg(msg);
		},
		// 显示提示信息
		'showMsg': function(msg) {
			$('#alertModalBody').html(msg);
			$('#alertModal').modal('show');
		}
	};
})(Gv || (Gv = {}));

// 游戏列表窗口对象
(function() {
	var WinContent = {
		'SELECT_GAME' : 0,
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
				['选定', function(gid) {
					WinContent.setGame(gid);
				}]
			]);
		},
		showGameInfo: function(gId) {
			console.log("要查看：", this.mName, " gId:", gId);
		},
		setGame: function(gId) {
			this.SELECT_GAME = gId;
			Gm.send('game set game ' + gId, function(jsondb) {
				if (jsondb.RES != true) {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
				} else {
					Gv.DialogMsg.showOkMsg("选择游戏操作成功！请选择要操作的分区。");
					$('#spMainGameName').text(jsondb.DBs);
					// 转向游戏分区选项
					Gv.Content.showContent('zoneList');
				}
			});
		}
	};
	Gv.Content.regContent('gameList', WinContent);
}());
// 分区列表
(function() {
	var WinContent = {
		'show': function() {
			$('#contGameList').show();
			Gm.send('game zones', function(jsondb) {
				WinContent.showDb(jsondb);
			});
		},
		'hide': function() {
			$('#contGameList').hide();
		},
		'showDb': function(jsondb) {
			if (jsondb.RES == true) {
				Gv.Content.createTable($('#contGameListTitle'), $('#contGameListHead'), $('#contGameListBody'), jsondb.DBs, [
					['选定', function(zId) {
						WinContent.setZone(zId);
					}]
				]);
			} else {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
			}
		},
		'setZone': function(zId) {
			Gm.send('game set zone ' + zId, function(jsondb) {
				if (jsondb.RES != true) {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
				} else {
					Gv.DialogMsg.showOkMsg("游戏分区选定成功！");
					Gv.showSelectGameAndZone(jsondb.DBs[0], jsondb.DBs[1]);
				}
			});
		}
	}
	Gv.Content.regContent('zoneList', WinContent);
	Gm.OBs.regOB('ZONE', WinContent);
}());
// 帐号列表
(function() {
	var UserContent = {
		// options
		//		act: list | search | disuser
		//		page:
		'show': function(options) {
			$('#contGameList').show();
			if (!options) {
				options = {};
			}
			console.log(options);
			page = 1;
			if (options['page']) {
				page = Math.floor(options['page']);
				if (page < 1) {
					page = 1;
				}
			}
			Gm.send('gameuser list ' + page, function(jsondb) {
				if (jsondb.RES != true) {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
					return;
				}
				Gv.Content.showTable(jsondb.DBs, [
					['编辑', function(guid) {
						console.log("选中GUID为：", guid);
					}]
				]);
			});
		},
		'hide': function() {
			$('#contGameList').hide();
		}
	}
	Gv.Content.regContent('userList', UserContent);
	// Gm.OBs.regOB('USERLIST', UserContent);
}());
