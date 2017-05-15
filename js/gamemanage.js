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

	// 玩家信息数据
	GM.DBs = {
		User: {
			id: 0,
			uid: '',
			name: ''
		},
		SelectGame: {
			game: {
				id: null,
				key: null,
				name: null
			},
			zone: {
				gameID: null,
				id: null,
				zoneID: null,
				zoneName: null,
			},
			getGameGid: function() { return this.game.id; },
			getGameKey: function() { return this.game.key; },
			getGameName: function() { return this.game.name; },
			getZoneName: function() { return this.zone.zoneName; },
			getInfo: function() {
				var gName = this.getGameName();
				if (gName == null) {
					return '未选定';
				}
				var zName = this.getZoneName();
				if (zName == null) {
					return gName;
				}
				return gName + ' -' + zName;
			},
			init: function(selectInfo) {
				if (selectInfo.game) {
					this.game = selectInfo.game;
				} else {
					this.game = {};
				}
				if (selectInfo.zone) {
					this.zone = selectInfo.zone;
				} else {
					this.zone = {};
				}
			}
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
					console.log("系统从服务器获取", game, " 的查询Key值");
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
	GM.GameCacheFields = {
		fields: {},
		getFields: function(fieldType, funcBack) {
			var game = GM.DBs.SelectGame.getGameKey();
			if (game == null) {
				Gv.showErrMsg("请先选择你要操作的游戏");
				return;
			}
			if (!this.fields[game]) {
				this.fields[game] = {};
			}
			if (!this.fields[game][fieldType]) {
				switch (fieldType) {
					case 'IP_ADD':
						var self = this;
						GM.send('ip addfield', function(jsondb) {
							if (jsondb.RES != true) {
								Gv.DialogMsg.showErrMsg(jsondb.MSG);
								return;
							}
							self.fields[game][fieldType] = jsondb.DBs;
							funcBack(jsondb.DBs);
						});
						break;
					case 'PAY_SEARCH':
						var self = this;
						GM.send('pay getsearch', function(jsondb) {
							if (jsondb.RES != true) {
								Gv.DialogMsg.showErrMsg(jsondb.MSG);
								return;
							}
							var arrSearch = [];
							for (var k in jsondb.DBs) {
								arrSearch.push([k, jsondb.DBs[k]]);
							}
							self.fields[game][fieldType] = arrSearch;
							funcBack(arrSearch);
						});
						break;
					case 'ZONE_ADD':
						var self = this;
						GM.send('game zonefield', function(jsondb) {
							if (jsondb.RES != true) {
								Gv.DialogMsg.showErrMsg(jsondb.MSG);
								return;
							}
							self.fields[game][fieldType] = jsondb.DBs;
							funcBack(jsondb.DBs);
						});
						break;
					default:
						Gv.DialogMsg.showErrMsg("没有这个缓存数据" + fieldType + "相应的接口！");
						return;
				}
				console.log("系统从服务器调用", game, " 的", fieldType, "字段数据")
			} else {
				funcBack(this.fields[game][fieldType]);
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
		Gv.LoadingIco.show(true);
		$.ajax({
			'url': 		this.serverPath,
			'dataType': 'json',
			'data': 	{'cmd': cmd},
			'success': 	function(jsondb){
				Gv.LoadingIco.show(false);
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
			Gv.Content.showContent('userList', {page: vPage}); break;
			case "PAY_LIST":
			Gv.Content.showContent('payList', {page: vPage});  break;
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
			Gm.DBs.SelectGame.init(tSelectGame);
			Gv.showSelectGameAndZone(Gm.DBs.SelectGame.getInfo());
			// 执行显示游戏列表
			if (Gm.DBs.SelectGame.getGameKey()) {
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
	Gv.showSelectGameAndZone = function(tName) {
		$('#spMainGameName').text(tName);
	};
	Gv.LoadingIco = {
		dLoadIco: null,
		show: function(isShow) {
			if (!this.dLoadIco) {
				this.dLoadIco = $('#imgLoading');
			}
		    if (isShow) {
		    	this.dLoadIco.show();
		    } else {
		    	this.dLoadIco.hide();
		    }
		}
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
				window.setTimeout(this.backFunc, 500);
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
						Gm.SetForbidden(self.key, function(jsondb) {
							Gm.seeGUID(self.key, function(jsondb) { Gv.UIEditBox.show(jsondb); });
						});
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
						self.doEditPassword();
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
	// 踢人下线
	Gv.UIOutPlayer = {
		obEdit: null,
		_init: function() {
			if (!this.obEdit) {
				this.obEdit = new Gv.CUIEditLine();
				$('#editBoxs').append(this.obEdit.getMainDiv());
				this.obEdit.setOptions({
					title: '请输入要被踢下线的角色名',
					btnName: '确定',
					func: function(value) {
						// console.log(value);
						Gm.send('game kick ' + value, function(jsondb) {
							if (!jsondb.RES) {
								Gv.DialogMsg.showErrMsg(jsondb.MSG);
								return;
							}
							Gv.DialogMsg.showOkMsg(jsondb.MSG);
							Gv.UIOutPlayer.obEdit.hide();
						});
					}
				});
			}
		},
		show: function() {
			this._init();
			this.obEdit.show()
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
					Gm.DBs.SelectGame.init(jsondb.DBs);
					// $('#spMainGameName').text(Gm.DBs.SelectGame.getInfo());
					Gv.showSelectGameAndZone(Gm.DBs.SelectGame.getInfo());
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
		obTable: null,
		_init: function() {
			if (this.obTable)
				return;
			this.obTable = new Gv.CContent();
			this.obTable.dTableTitle.hide();
			$('#contZoneList').append(this.obTable.getMainDiv());
			$('#conZoneBtnAdd').bind('click', function() {
				Gm.GameCacheFields.getFields('ZONE_ADD', function(vFields) {
					Gv.UIEditer.show({
						title: '添加新的游戏分区',
						fields: vFields,
						func: function(args) {
							WinContent.doAddZone(args);
						}
					});
				});
			});
		},
		doAddZone: function(args) {
			Gm.send('game addzone ' + args.join(','), function(jsondb) {
				if (jsondb.RES != true) {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
					return;
				}
				Gv.Content.showContent('zoneList');
				Gv.UIEditer.hide();
			});
		},
		show: function() {
			this._init();
			$('#contZoneList').show();
			Gm.send('game zones', function(jsondb) {
				WinContent.showDb(jsondb);
			});
		},
		hide: function() {
			$('#contZoneList').hide();
		},
		showDb: function(jsondb) {
			if (jsondb.RES == true) {
				WinContent.obTable.showTable(jsondb.DBs, [
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
					Gm.DBs.SelectGame.init(jsondb.DBs);
					Gv.showSelectGameAndZone(Gm.DBs.SelectGame.getInfo());
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
		obSearch: null,

		_init: function() {
			if (this.obTable)
				return;
			this.obTable = new Gv.CContent();
			this.obTable.dTableTitle.hide();
			var divMain = $('#conGameUserList');
			divMain.append(this.obTable.getMainDiv());
			this.obSearch = new Gv.CBoxSearch();
			divMain.find('h4').append(this.obSearch.getMainDiv());
			// this.obSearch.showSearch()
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
				this.search = options.search;
			}
			page = 1;
			if (options.page) {
				page = Math.floor(options.page);
				if (page < 1)
					page = 1;
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
			Gm.GameUserSearch.getSearch(Gm.DBs.SelectGame.getGameKey(), function(searchKeys) {
				searchOption.options = searchKeys;
				UserContent.obSearch.showSearch(searchOption);
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
// 充值列表
(function() {
	var UserPayContent = {
		obTable: null,
		obSearch: null,
		divMain: null,
		page: 1,
		search: null,
		nowZone: false,

		_init: function() {
			if (this.obTable)
				return;
			this.obTable = new Gv.CContent();
			this.obSearch = new Gv.CBoxSearch();
			this.divMain = $('#conUserPayList');
			this.divMain.append(this.obTable.getMainDiv());
			this.divMain.find('h4').append(this.obSearch.getMainDiv());
		},

		show: function(options) {
			this._init();
			this.divMain.show();
			if (!options) {
				options = {};
			} else {
				for (var k in options) {
					switch (k) {
						case 'page':
							this.page = Math.floor(options.page);
							if (this.page < 1)
								this.page = 1;
							break;
						case 'nowZone':
							if (options.nowZone)
								this.nowZone = true;
							else
								this.nowZone = false;
							break;
						case 'search':
							this.search = options.search;
							break;
					}
				}
			}
			Gm.send('pay list ' + this.page + ' ' + this.nowZone + ' ' + this.search, function(jsondb) {
				UserPayContent.showDb(jsondb);
			});
		},

		showDb: function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			this.obTable.showTable(
				jsondb.DBs,
				[['玩家信息', function(uid){
					Gm.send('gameuser seeuid ' + uid, function(jsondb) {
						if (jsondb.RES != true) {
							Gv.DialogMsg.showErrMsg(jsondb.MSG);
							return;
						}
						Gv.UIEditBox.show(jsondb.DBs);
					});
				}]],
				jsondb.CMD);
			var searchOption = {
				placeholder: "查找订单号",
				func: function(findType, findValue) {
					UserPayContent.doSerach(findType, findValue);
				}
			};
			if (this.search) {
				var searchKeys = this.search.split("=");
				if (searchKeys.length == 2) {
					searchOption.searchKey = searchKeys[0];
					searchOption.searchVal = searchKeys[1];
				}
			}
			Gm.GameCacheFields.getFields('PAY_SEARCH', function(searchKeys) {
				searchOption.options = searchKeys;
				UserPayContent.obSearch.showSearch(searchOption);
			});
		},

		doSerach: function(findType, findValue) {
			this.show({page: 1, search: findType + '=' + findValue});
		},

		hide: function() {
			this.divMain.hide();
		}
	};
	Gv.Content.regContent('payList', UserPayContent);
})();
// 统计列表
(function() {
	var UserPayCountContent = {
		obTableMon: null,
		obTableCount: null,
		obTableDay: null,
		divMain: null,

		_init: function() {
			// conUserPayCount
			if (this.obTableMon)
				return;
			this.obTableMon   = new Gv.CContent();
			this.obTableCount = new Gv.CContent();
			this.obTableDay   = new Gv.CContent();
			this.divMain      = $('#conUserPayCount');
			this.divMain.append(this.obTableMon.getMainDiv());
			this.divMain.append(this.obTableCount.getMainDiv());
			this.divMain.append(this.obTableDay.getMainDiv());
		},

		show: function(options) {
			this._init();
			this.obTableDay.hide();
			Gm.send('pay countmons', function(jsondb) { UserPayCountContent.showDb(jsondb); });
			this.divMain.show();
		},

		showDb: function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			this.obTableMon.showTable(jsondb.DBs.K_MONTHS, [['每日充值合计',
				function(vMonth) {
					UserPayCountContent.doDays(vMonth);
				}]]);
			this.obTableCount.showTable(jsondb.DBs.K_COUNT)
		},

		doDays: function(mon) {
			Gm.send('pay countdays ' + mon, function(jsondb) {
				UserPayCountContent.showDays(jsondb);
			});
		},

		showDays: function(jsondb) {
			if (jsondb.RES != true) {
				Gv.DialogMsg.showErrMsg(jsondb.MSG);
				return;
			}
			this.obTableDay.showTable(jsondb.DBs);
			this.obTableDay.show();
		},

		hide: function() {
			this.divMain.hide();
		}

	}
	Gv.Content.regContent('payCount', UserPayCountContent);
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
			$('#conIpBtnAdd').bind('click', function(){
				Gm.GameCacheFields.getFields('IP_ADD', function(vFields) {
					Gv.UIEditer.show({
						title: '添加黑名单IP地址',
						fields: vFields,
						func: function(args) {
							IpContent.doAddIP(args);
						}
					});
				});
			});
		},
		doAddIP: function(args) {
			console.log(args);
			var sendVal = args; //.replace(/\s+/g, "");
			console.log(args, " now:", sendVal);
			Gm.send('ip add ' + sendVal, function(jsondb) {
				if (jsondb.RES == true) {
					Gv.DialogMsg.showOkMsg('操作成功！');
					Gv.UIEditer.hide();
					// 刷新界面
					IpContent.show();
				} else {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
				}
			});
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
				['删除', function(args) { IpContent.showDeleteInfo(args[0], args[1])}, function(db) { return [db[0], db[1]] }]
			]);
		},
		showDeleteInfo: function(ipid, ipVal) {
			console.log(ipid, ipVal);
			Gv.DialogMsg.showInquiry("确定要将" + ipVal + "地址从IP黑名单中删除吗？", function(){ IpContent.doDeleteInfo(ipid); });
		},
		doDeleteInfo: function(ipid) {
			// console.log("执行删除", ipid);
			Gm.send('ip delete ' + ipid, function(jsondb) {
				if (jsondb.RES != true) {
					Gv.DialogMsg.showErrMsg(jsondb.MSG);
					return;
				}
				Gv.DialogMsg.showOkMsg(jsondb.MSG);
				IpContent.show();
			});
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
                                    <select class="form-control" style="width: 120px; margin-left: 5px;border-radius: 0px;display: none;"></select>\
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
		this.dPages = $('<ul class="pagination"></ul>');
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
		// 显示分页
		this.showNavpage(cmdType, showTableDb.navpage);
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
	_proto_.setTitle = function(title) {
		this.dTableTitle.text(title);
	};
	// 显示分页
	_proto_.showNavpage = function(cmdType, parames) {
		var navPage = this.dPages;
		if (parames.pages.length < 1) {
			return;
		}
		navPage.empty();
		navPage.append($("<li class='previous'><a href='javascript:Gm.navPage(\"" + cmdType + "\", 1);'>&larr;Top</a></li>"));
		if (parames.nowpage > 1) {
			var pre = parames.nowpage - 1;
			navPage.append($("<li><a href='javascript:Gm.navPage(\"" + cmdType + "\", " + pre + ");'>&laquo;</a></li>"));
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
	};
	_proto_.show = function() {
		this.mainDiv.show();
	};
	_proto_.hide = function() {
		this.mainDiv.hide();
	};
})(Gv || (Gv = {}));

(function(Gv) {
	Gv.CBoxSearch = function() {
		this.mainDiv = $('<div class="input-group col-md-3" style="width: 30%;float: right;margin-right: 0px;display:none;"></div>');
		this.dInput = $('<input type="text" class="form-control" placeholder="请输入查找值" / >')
		this.mainDiv.append(this.dInput);
		this.dGroup = $('<span class="input-group-btn" style="width: 180px;"></span>');
		this.mainDiv.append(this.dGroup);
		this.dSelect = $('<select class="form-control" style="width: 100px; margin-left: 5px;border-radius: 0px;"></select>');
		this.dButton = $('<button class="btn btn-info btn-search" style="margin-left:5px;background-color: #555;border-color: #333;width: 80px;">查找</button>');
		this.dGroup.append(this.dSelect);
		this.dGroup.append(this.dButton);
	}
	var _proto_ = Gv.CBoxSearch.prototype;
	// 添加查找对象
	//	parames {
	//		options: {[val, text]},
	//		selectKey: keyVal,
	//		func: function(searchVal)
	//	}
	//
	_proto_.showSearch = function(parames) {
		if (!parames)
			parames = {};
		if (parames.placeholder) {
			this.dInput.attr("placeholder", parames.placeholder);
		} else {
			this.dInput.attr("placeholder", "请输入查找值");
		}
		this.dSelect.empty();
		if (parames.options && parames.options.length > 0) {
			for (var k in parames.options) {
				var arr = parames.options[k];
				this.dSelect.append("<option value='" + arr[0] + "'>" + arr[1] + "</option>");
			}
			if (parames.searchKey) {
				this.dSelect.val(parames.searchKey);
			}
			if (parames.options.length > 1) {
				this.setSelectShow();
			} else {
				this.dSelect.val(parames.options[0][0]);
				this.setSelectHide();
			}
		} else {
			this.setSelectHide();
		}
		if (parames.searchVal) {
			this.dInput.val(parames.searchVal);
		} else {
			this.dInput.val("");
		}
		// 绑定click事件
		this.dButton.unbind("click");
		if (parames.func) {
			var self = this;
			(this.dButton.bind("click", function(){
				parames.func(self.dSelect.val(), self.dInput.val());
			}));
		}
		this.mainDiv.show();
	};
	_proto_.setSelectShow = function() {
		this.dSelect.show();
		this.dGroup.css('width', '180px');
	};
	_proto_.setSelectHide = function() {
		this.dSelect.hide();
		this.dGroup.css('width', '80px');
	};
	_proto_.getMainDiv = function() {
		return this.mainDiv;
	};
})(Gv || (Gv = {}));
// 编辑界面
(function(Gv) {
	Gv.CEditBox = function() {
		this.mainDiv = $('<div class="modal fade" id="modaEditBox" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="false" data-keyboard="false"></div>');
		var tMain = $('<div class="modal-dialog"></div>');
		this.mainDiv.append(tMain);
		var tContent = $('<div class="modal-content"></div>');
		tMain.append(tContent);
		var tHead = $('<div class="modal-header">\
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
                        <h4 class="modal-title">添加要被屏蔽的IP地址</h4>\
                    </div>');
		tContent.append(tHead);
		var tBody = $('<div class="modal-body"></div>');
		tContent.append(tBody);
		this.dForm = $('<div class="form-horizontal" role="form"></div>');
		tBody.append(this.dForm);
		var tFooter = $('<div class="modal-footer"></div>');
		tContent.append(tFooter);
		this.btnSave = $('<button type="button" class="btn btn-primary">保存</button>');
		tFooter.append(this.btnSave);
		tFooter.append($('<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>'));
		this.backFunc = null;
		var self = this;
		this.btnSave.bind('click', function(){ self.doSave(); });
	}
	var _proto_ = Gv.CEditBox.prototype;
	_proto_.getMainDiv = function() {
		return this.mainDiv;
	};
	// 显示界面
	//	options {
	//		'title': '标题'
	//		'fields': [['id', '名称', FIELD_TYPE]]
	//		'func': click function
	// 	}
	//
	_proto_.show = function(options) {
		if (!options)
			options = {};
		this._clear();
		for (var k in options) {
			switch (k) {
				case 'title':
					this._setTitle(options.title);
					break;
				case 'fields':
					this._createField(options.fields);
					break;
				case 'func':
					this.backFunc = options.func;
					break;
			}
		}
		this.mainDiv.modal("show");
	};
	_proto_.hide = function() {
		this.mainDiv.modal("hide");
	};
	_proto_._setTitle = function(titleVal) {
		this.mainDiv.find('h4').text(titleVal);
	};
	_proto_._clear = function() {
		this.dForm.empty();
		this.backFunc = null;
	};
	_proto_._createField = function(fields) {
		for (var i = 0; i < fields.length; i++) {
			var arr = fields[i];
			var inputType = '';
			switch (arr[2]) {
				case 'FIELD_TEXT':
				default:
					inputType = '<input type="text" name="' + arr[0] + '" class="form-control" />';
			}
			this.dForm.append($('<div class="form-group"><label class="col-sm-2 control-label" style="width:120px;">' + arr[1] + '</label><div class="col-sm-10" style="width:400px;">' + inputType + '</div></div>'));
		}
	};
	_proto_.doSave = function() {
		var args = [];
		var arrInput = this.dForm.find("input");
		for (var i = 0; i < arrInput.length; i++) {
			var ob = arrInput[i];
			args.push(ob.name + "=" + ob.value);
		}
		if (this.backFunc) {
			this.backFunc(args);
		}
	};
	// 已生成的UIBox
	Gv.UIEditer = {
		obEditBox: null,
		_init: function() {
			if (!this.obEditBox) {
				this.obEditBox = new Gv.CEditBox();
				$('#editBoxs').append(this.obEditBox.getMainDiv());
			}
		},
		show: function(options) {
			this._init();
			this.obEditBox.show(options);
		},
		hide: function() {
			this._init();
			this.obEditBox.hide();
		}
	};
})(Gv || (Gv = {}));

(function(Gv) {
	Gv.CUIEditLine = function() {
		this.func = null;

		this.divMain = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>');
		var modal = $('<div class="modal-dialog" style="margin-top: 100px;width: 500px;"></div>');
		this.divMain.append(modal);
		var content = $('<div class="modal-content"></div>');
		modal.append(content);
		var head   = $('<div class="modal-header"></div>');
		content.append(head);
		head.append($('<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'));
		this.title = $('<h4 class="modal-title">-</h4>');
		head.append(this.title);
		var body = $('<div class="modal-body"></div>');
		content.append(body);
		var row = $('<div class="row" style="margin-left: 70px;margin-right: 70px;">');
		body.append(row);
		this.txtInput = $('<input type="text" class="form-control" value="" style="float: left;width: 230px;" />');
		row.append(this.txtInput);
		this.butOk = $('<button class="btn btn-danger" type="button" style="float: right;width:80px;">-</button>');
		row.append(this.butOk);
		body.append($('<p><br /></p>'));
		var self = this;
		this.butOk.bind('click', function(){ self._do(); });
	}
	var _proto_ = Gv.CUIEditLine.prototype;
	// options
	// title, btnName, func
	_proto_.show = function(options) {
		this.setOptions(options);
		this.txtInput.val('');
		this.divMain.modal('show');
	};
	_proto_.hide = function() {
		this.divMain.modal('hide');
	}
	_proto_.setOptions = function(options) {
		if (!options)
			options = {};
		for (var k in options) {
			switch (k) {
				case 'title':
					this.title.text(options.title); break;
				case 'btnName':
					this.butOk.text(options.btnName); break;
				case 'func':
					this.func = options.func; break;
			}
		}
	};
	_proto_.getMainDiv = function() {
		return this.divMain;
	};
	_proto_._do = function() {
		if (this.func) {
			this.func(this.txtInput.val());
		}
	}
})(Gv || (Gv = {}));
