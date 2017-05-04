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
		User: {
			id: 0,
			uid: '',
			name: ''
		},
		SelectGame: {
			game: null,
			zone: null
		}
	};
	// 获取查询KEY
	GM.GameUserSearch = {
		searchs: {},
		getSearch: function(game, funcBack) {
			if (this.searchs[game]) {
				funcBack(this.searchs[game]);
			} else {
				// 返回
				GM.send("gameuser getsearch", function(jsondb) {
					// console.log("获取新的", game, " 的查询Key值");
					if (jsondb.RES != true) {
						Gv.DialogMsg.showErrMsg(jsondb.MSG);
						return;
					}
					var arrSearch = [];
					for (var k in jsondb.DBs) {
						arrSearch.push([k, jsondb.DBs[k]]);
					}
					GM.GameUserSearch.searchs[game] = arrSearch;
					funcBack(GM.GameUserSearch.searchs[game]);
				});
			}
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
		// if (jsondb.RES != true) {
		// 	Gv.DialogMsg.showErrMsg(jsondb.MSG);
		// 	return;
		// }
		backFunc(jsondb);
	};
	GM.seeGUID = function(guid, backFunc) {
		this.send("gameuser see " + guid, function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			backFunc(jsondb.DBs);
		});
	};
	GM.navPage = function(cmdType, vPage) {
		switch (cmdType) {
			case "GAMEUSER_LIST":
			Gv.Content.showContent('userList', {page: vPage});
			break;
		}
	};
	GM.editPassword = function(uid, newpassword) {
		this.send("gameuser editpass " + uid + ' ' + newpassword, function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			Gv.DialogMsg.showOkMsg(jsondb.MSG);
		});
	};
	GM.SetForbidden = function(uid, func) {
		this.send("gameuser setforbidden " + uid, function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
			} else {
				Gv.DialogMsg.showOkMsg(jsondb.MSG);
				if (func) {
					func(jsondb);
				}
			}
		});
	};
})(Gm || (Gm = {}));

// GameManageView类
var Gv;
(function(Gv) {
	Gv.name = 'TEST';
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
		nowContent: null,
		arrContent: {},
		// 显示不同区的内容
		showContent: function(cType, args) {
			if (!this.arrContent[cType]) {
				return;
			}
			if (this.nowContent)
				this.nowContent.hide();
			this.arrContent[cType].show(args);
			this.nowContent = this.arrContent[cType];
		},
		// 注册正文内空对象
		regContent: function(cType, ob) {
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
		createTable: function(dTitle, dHead, dBody, showDb, actionDb) {
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

		showTable: function(showTableDb, actionDb, cmdType) {
			$('#contGameListSearch').hide();
			$('#contGameListPage').hide();
			this.createTable($('#contGameListTitle'), $('#contGameListHead'), $('#contGameListBody'), showTableDb, actionDb);
			// 显示分页
			this.showNavpage(cmdType, showTableDb.navpage);
		},
		// 添加查找对象
		//	parames {
		//		options: {[val, text]},
		//		selectKey: keyVal
		//	}
		//
		showSearch: function(parames) {
			var idName = '#contGameListSearch';
			if (!parames)
				parames = {};
			if (parames.placeholder) {
				$(idName).find('input').attr("placeholder", parames.placeholder);
			} else {
				$(idName).find('input').attr("placeholder", "请输入查找值");
			}
			var tSelect = $(idName).find('select');
			tSelect.empty();
			if (parames.options && parames.options.length > 0) {
				for (var k in parames.options) {
					var arr = parames.options[k];
					tSelect.append("<option value='" + arr[0] + "'>" + arr[1] + "</option>");
				}
				if (parames.searchKey) {
					tSelect.val(parames.searchKey);
				}
				if (parames.options.length > 1) {
					$(idName).find("select").show();
				} else {
					$(idName).find("select").val(parames.options[0][0]);
					$(idName).find("select").hide();
				}
			} else {
				tSelect.hide();
			}
			// 绑定click事件
			$(idName).find('button').unbind("click");
			if (parames.searchVal) {
				$(idName).find('input').val(parames.searchVal);
			} else {
				$(idName).find('input').val("");
			}
			if (parames.func) {
				($(idName).find('button').bind("click", function(){
					parames.func($(idName).find("select").val(), $(idName).find("input").val());
				}));
			}
			$(idName).show();
		},
		// 显示导航分页
		showNavpage: function(cmdType, parames) {
			var navPage = $('#contGameListPage');
			if (parames.pages.length < 1) {
				return;
			}
			navPage.empty();
			navPage.append($("<li class='previous'><a href='javascript:Gm.navPage(\"" + cmdType + "\", 1);'>&larr;Top</a></li>"));
			if (parames.nowpage > 1) {
				navPage.append($("<li><a href='javascript:Gm.navPage(\"" + cmdType + "\", 1);'>&laquo;</a></li>"));
			} else {
				navPage.append($("<li class='previous disabled'><a href='javascript:void(0);'>&laquo;</a></li>"));
			}
			for (var k in parames.pages) {
				var tPage = parames.pages[k];

				if (tPage == parames.nowpage) {
					var t = $("<li><a href='javascript:void(0);'>" + tPage + "</a></li>");
					t.attr("class", "active");
				} else {
					var t = $("<li><a href='javascript:Gm.navPage(\"" + cmdType + "\", " + tPage + ");'>" + tPage + "</a></li>");
				}
				navPage.append(t);
			}
			if (parames.nowpage  < parames.max) {
				var next = parames.nowpage + 1;
				navPage.append($("<li><a href='javascript:Gm.navPage(\"" + cmdType + "\", " + next  + ");'>&raquo;</a></li>"));
			} else {
				navPage.append($("<li class='previous disabled'><a href='javascript:void(0);'>&raquo;</a></li>"));
			}
			navPage.append($("<li class='next'><a href='javascript:Gm.navPage(\"" + cmdType + "\", " + parames.max + ");'>Last&rarr;</a></li>"));
			navPage.show();
		}
	};
	// 显示提示信息
	Gv.DialogMsg = {
		backFunc: null,
		// 显示带有勾选的按钮
		showOkMsg: function(msg) {
			$('#alertModalTitle').text("提示");
			// $('#alertModalIcon').attr("class", "glyphicon glyphicon-ok-circle");
			$('#alertModalBody').css('color', "	#000000");
			this.showMsg(msg);
		},
		showErrMsg: function(msg) {
			$('#alertModalTitle').text("错误");
			$('#alertModalBody').css('color', "#B22222");
			this.showMsg(msg);
		},
		// 显示提示信息
		showMsg: function(msg) {
			$('#alertModalBtnOK').hide();
			$('#alertModalBtnCancel').text("关闭");
			$('#alertModalBody').html(msg);
			$('#alertModal').modal('show');
		},
		// 显示询问提示框
		showInquiry: function(msg, backFunc) {
			$('#alertModalBtnOK').show();
			$('#alertModalBtnCancel').text("取消");
			this.backFunc = backFunc;
			$('#alertModalBody').css('color', "	#eea236");
			$('#alertModalBody').text(msg);
			$('#alertModalTitle').text("询问");
			$('#alertModal').modal('show');
		},
		doInquiry: function() {
			if (this.backFunc) {
				this.backFunc();
			}
			$('#alertModal').modal('hide');
		}
	};
	// 显示查看详细页面
	Gv.UIEditBox = (function() {
		var divMainName = '#modaEditUserBox';
		var btns = {
			'F_EDIT_PASS': '#modaEditUserBoxBtnPass',
			'F_FORBIDDEN': '#modaEditUserBoxBtnForbidden'
		};
		var uiEditBox = function(){
			this.tlbBody = null;
			this.key      = 0;
		};
		var _proto_ = uiEditBox.prototype;
		_proto_._init = function() {
			if (!this.tlbBody) {
				var self = this;
				this.tlbBody = $('#modaEditUserBoxBody');
				$(btns['F_EDIT_PASS']).bind('click', function(){ Gv.UIEditPassword.show(Gv.UIEditBox.key); });
				$(btns['F_FORBIDDEN']).bind('click', function(){
					Gv.DialogMsg.showInquiry("对玩家进封号和解封号操作，如果已被封号则解除封号反之则进行封号！是否进行操作？", function() {
						window.setTimeout(function() {
							Gm.SetForbidden(self.key, function(jsondb) {
								Gm.seeGUID(self.key, function(jsondb) { Gv.UIEditBox.show(jsondb); });
							});
						}, 500);
					});
				});
			}
			// 清除内容
			this.tlbBody.empty();
			// 隐藏所有按钮
			for (var bk in btns) {
				$(btns[bk]).hide();
			}
			this.key = 0;
		};
		_proto_.show = function(dbInfo) {
			this._init();
			$('#modaEditUserBox').modal('show');
			for (var k in dbInfo) {
				switch (k) {
					case "dbs":
						// this._showDbs(dbInfo[k]);
						var dbs = dbInfo[k];
						for (var j in dbs) {
							var db = dbs[j];
							switch (db[0]) {
								case 'TEXT_RED':
									this.tlbBody.append($("<tr><td style='font-weight: bold;width: 150px;'>" + j + "</td><td style='color: #FF0000;'>" + db[1] + "</td></tr>"));
									break;
								default:
									this.tlbBody.append($("<tr><td style='font-weight: bold;width: 150px;'>" + j + "</td><td>" + db[1] + "</td></tr>"));
							}
						}
						break;
					case "func":
						var funcs = dbInfo[k];
						for (var l in funcs) {
							if (btns[funcs[l]]) {
								$(btns[funcs[l]]).show();
							}
						}
						break;
					case "key":
						this.key = dbInfo[k];
					break;
				}
			}
		};
		return new uiEditBox;
	}());
	// 修改玩家帐号密码
	Gv.UIEditPassword = {
		divMain: null,
		key: null,
		show: function(key) {
			if (this.divMain == null) {
				this.divMain = $('#modaEditUserBoxPass');
				var self = this;
				$("#modaEditUserBtn").bind("click", function() {
					Gv.DialogMsg.showInquiry("确定要更改这名玩家帐号的登入密码？", function() {
						window.setTimeout(function(){ self.doEditPassword(); }, 500);
					});
				});
			}
			this.key = key;
			this.divMain.modal('show');
		},
		doEditPassword: function() {
			if (this.key) {
			   	Gm.editPassword(this.key, $('#modaEditUserBoxPassText').val());
			}
			this.divMain.modal("hide");
		}
	};
})(Gv || (Gv = {}));

// 游戏列表窗口对象
(function() {
	var WinContent = {
		SELECT_GAME : 0,
		show: function() {
			$('#contGameList').show();
			Gm.send('game list', function(jsondb) {
				WinContent.showDb(jsondb);
			});
		},
		hide: function() {
			$('#contGameList').hide();
		},
		showDb: function(jsondb) {
			Gv.Content.showTable(jsondb.DBs, [
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
})();
// 分区列表
(function() {
	var WinContent = {
		show: function() {
			$('#contGameList').show();
			Gm.send('game zones', function(jsondb) {
				WinContent.showDb(jsondb);
			});
		},
		hide: function() {
			$('#contGameList').hide();
		},
		showDb: function(jsondb) {
			if (jsondb.RES == true) {
				Gv.Content.showTable(jsondb.DBs, [
					['选定', function(zId) {
						WinContent.setZone(zId);
					}]
				]);
			} else {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
			}
		},
		setZone: function(zId) {
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
})();
// 帐号列表
(function() {
	var UserContent = {
		act: 'list',
		search: '',
		nowPage: 1,
		obTable: null,

		_init: function() {
			if (this.obTable)
				return;
			this.obTable = new Gv.CContent();
			this.obTable.dTableTitle.hide();
			$('#conGameUserList').append(this.obTable.getMainDiv());
		},
		// options
		// 	search: [val]
		//	act: 	list | disuser
		//	page: 	[val]
		show: function(options) {
			this._init();
			$('#conGameUserList').show();
			if (!options) {
				options = {};
			}
			console.log(options);
			switch (options.act) {
				case 'list':
				case 'disuser':
					this.act = options.act;
					break;
			}
			// 显示副标题
			var t = '帐号列表';
			if (this.act == 'disuser')
				t = '被封号的帐号列表';
			$('#conGameUserList').find('small').text(t);
			if (options.search || options.search == "") {
				console.log("search ", options.search);
				this.search = options.search;
			}
			page = 1;
			if (options.page) {
				page = Math.floor(options.page);
				if (page < 1) {
					page = 1;
				}
			}
			this.nowPage = page;
			var self = this;
			Gm.send('gameuser ' + this.act + ' ' + page + ' ' + this.search, function(jsondb) { self.showDb(jsondb); });
		},
		showDb: function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			this.obTable.showTable(
				jsondb.DBs,
				[['查看', function(vGuid) {
					Gm.seeGUID(vGuid, function(jsondb) { Gv.UIEditBox.show(jsondb); });
				}]],
				jsondb.CMD);
			var searchOption = {
				placeholder: "查找用户帐号",
				func: function(findType, findValue) {
					UserContent.doSerach(findType, findValue);
				}
			};
			if (this.search) {
				var searchKeys = this.search.split("=");
				if (searchKeys.length == 2) {
					searchOption.searchKey = searchKeys[0];
					searchOption.searchVal = searchKeys[1];
				}
			}
			// 显示查找键值
			Gm.GameUserSearch.getSearch(Gm.DBs.SelectGame.game, function(searchKeys) {
				searchOption.options = searchKeys;
				UserContent.obTable.showSearch(searchOption);
			});
		},
		hide: function() {
			$('#conGameUserList').hide();
		},
		doSerach: function(findType, findValue) {
			// 查找的命令
			//	gameuser <act> <page> <search findType=findValue>
			this.show({
				search: findType + "=" + findValue,
				act: this.act,
				page: 1
			});
		}
	}
	Gv.Content.regContent('userList', UserContent);
	Gm.OBs.regOB('USERLIST', UserContent);
})();

// IP列表
(function() {
	var IpContent = {
		obTable: null,
		page: 1,
		search: '',

		_init: function() {
			if (this.obTable)
				return;
			this.obTable = new Gv.CContent();
			this.obTable.dTableTitle.hide();
			$('#contIpList').append(this.obTable.getMainDiv());
		},
		show: function(options) {
			this._init();
			$('#contIpList').show();
			if (!options) {
				options = {};
			}
			if (options.page) {
				this.page = options.page;
			}
			if (options.search) {
				this.search = options.search;
			}
			var self = this;
			Gm.send('ip list ' + this.page + ' ' + this.search, function(jsondb){
				self.showDb(jsondb);
			});
		},
		showDb: function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			this.obTable.showTable(jsondb.DBs, [
				// 操作名称, 操作函数function(key, args), [获取参数的函数 , function(db) { return [db[0], db[1]] }]
				['删除', function(args) { console.log(args);}]
			]);
		},
		hide: function() {
			$('#contIpList').hide();
		}
	}
	Gv.Content.regContent('ipList', IpContent);
	Gm.OBs.regOB('IPLIST', IpContent);
})();

// 窗口表格对象
(function(Gv) {
	Gv.CContent = function() {
		this.mainDiv = $('<div></div>');
		this.dTable   = $('<table class="table table-striped"></table>');
		this.mainDiv.append(this.dTable);
		this.dTableTitle  = $('<caption style="font-weight: bold;font-size: 16px;"></caption>');
		this.dTable.append(this.dTableTitle);
		this.dSearch = $('<caption style="display: none;">\
                            <div class="input-group col-md-3" style="width: 30%;">\
                                <input type="text" class="form-control" placeholder="请输入查找值" / >\
                                <span class="input-group-btn">\
                                    <select class="form-control" style="width: 120px; margin-left: 5px;border-radius: 0px;"></select>\
                                    <button class="btn btn-info btn-search" style="margin-left:5px;background-color: #555;border-color: #333;width: 90px;">查找</button>\
                                </span>\
                            </div>\
                        </caption>');
		this.dTable.append(this.dSearch);
		this.dTableHead = $('<thead></thead>');
		this.dTable.append(this.dTableHead);
		this.dTableBody = $('<tbody></tbody>');
		this.dTable.append(this.dTableBody);
		// 分页
		var t = $('<div style="text-align: center;"></div>');
		this.mainDiv.append(t);
		this.dPages = $('<ul></ul>');
		t.append(this.dPages);
	};
	var _proto_ = Gv.CContent.prototype;
	_proto_.getMainDiv = function() {
		return this.mainDiv;
	}
	_proto_.showTable = function(showTableDb, actionDb, cmdType) {
		// 隐藏查找和分页
		this.dSearch.hide();
		this.dPages.hide();
		// 创建Table信息
		this.createTable(showTableDb, actionDb);
	};
	// 创建表格
	_proto_.createTable = function(showDb, actionDb) {
		this.dTableTitle.text(showDb.title);
		this.dTableHead.empty();
		this.dTableBody.empty();
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
		this.dTableHead.append($(sHead));
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
					var actdb = actionDb[i];
					var func = actdb[1];
					var arg  = (actdb[2]) ? actdb[2](arr) : arr[showDb.key];
					sa = $('<a href="javascript:void(0);">' + actionDb[i][0] + '</a>');
					sa[0].doFunc = func;
					sa[0].doArg  = arg;
					sa.click(function(e){ this.doFunc(this.doArg); });
					st.append(sa);
				}
				tr.append(st);
			}
			this.dTableBody.append(tr);
		}
	};
	// 显示查找值
	_proto_.showSearch = function(parames) {
		if (!parames) {
			return;
		}
		if (parames.placeholder) {
			this.dSearch.find('input').attr("placeholder", parames.placeholder);
		} else {
			this.dSearch.find('input').attr("placeholder", "请输入查找值");
		}
		var tSelect = this.dSearch.find('select');
		tSelect.empty();
		if (parames.options && parames.options.length > 0) {
			for (var k in parames.options) {
				var arr = parames.options[k];
				tSelect.append("<option value='" + arr[0] + "'>" + arr[1] + "</option>");
			}
			if (parames.searchKey) {
				tSelect.val(parames.searchKey);
			}
			if (parames.options.length > 1) {
				this.dSearch.find("select").show();
			} else {
				this.dSearch.find("select").val(parames.options[0][0]);
				this.dSearch.find("select").hide();
			}
		} else {
			tSelect.hide();
		}
		// 绑定click事件
		this.dSearch.find('button').unbind("click");
		if (parames.searchVal) {
			this.dSearch.find('input').val(parames.searchVal);
		} else {
			this.dSearch.find('input').val("");
		}
		if (parames.func) {
			var self = this;
			(this.dSearch.find('button').bind("click", function(){
				parames.func(self.dSearch.find("select").val(), self.dSearch.find("input").val());
			}));
		}
		this.dSearch.show();
	}
})(Gv || (Gv = {}));
