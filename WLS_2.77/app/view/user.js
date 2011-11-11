/**
 * 用户模块
 * */
wls.user = Ext.extend(wls, {
	
	myUser : {
		privilege : null,
		group : null,
		subject : null,
		username : null,
		id : null,
		money : null,
		credits : null
	},

	/**
	 * 生成一个弹出框来让用户输入登录信息,如果用户登录成功,系统会重刷新一次
	 * 用来重新获取用户的数据
	 * 
	 * @return Ext.form.FormPanel
	 */
	getLogin : function(domid) {
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id : 'wls_melogin_form',
			labelWidth : 75,
			frame : true,
			height: 265,
			bodyStyle : 'padding:5px 5px 0',
			width : "100%",
			defaults : {
				width : 100
			},
			defaultType : 'textfield',

			items : [{
						fieldLabel : il8n.user.username,
						width : 150,
						vtype : "alphanum",
						name : 'username',
						allowBlank : false
					}, {
						fieldLabel : il8n.user.password,
						width : 150,
						vtype : "alphanum",
						name : 'password',
						inputType : 'password',
						allowBlank : false
					}, {
						fieldLabel : il8n.normal.CAPTCHA  ,
						width : 150,
						vtype : "alphanum",
						enableKeyEvents : true,
						name : 'CAPTCHA',
						allowBlank : false,
						id : 'CAPTCHA'
					}, new Ext.BoxComponent({
						//验证码部分
						fieldLabel : "<span style='color:red'>"+il8n.normal.CAPTCHA+"</span>",
						height : 32, 
						autoEl : {
							tag : 'div',
							html : '<img style="width:100px; height:28px;" id="captcha" src="'
									+ thisObj.config.libPath
									+ 'securimage/securimage_show.php" alt="CAPTCHA Image" />'
						}
					})],

			buttons : [{
						text : il8n.user.login,
						handler : function() {
							thisObj.login();
						}
					}, {
						text : il8n.normal.refresh + il8n.normal.CAPTCHA,
						handler : function() {
							$('#captcha').attr(
									"src",
									thisObj.config.libPath + 'securimage/securimage_show.php?temp='
											+ Math.random());
						}
					}, {
						text : il8n.user.register,
						handler : function() {
							thisObj.register();
						}
					}

			]
		});
		Ext.getCmp('CAPTCHA').on('keyup', function(obj, e) {
					if (e.getKey() == '13') {
						thisObj.login();
					}
				});
		return form;
	},
	
	/**
	 * 弹出一个用户注册页面
	 * 这个功能只有在选择了 独立安装模式 的情况下才能用
	 * 集成运行模式下,这里的注册信息不会同步到目标系统中的
	 * 
	 * @return Ext.form.FormPanel
	 * */
	register : function() {
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id : 'wls_meregister_form',
			labelWidth : 75,
			frame : true,
			bodyStyle : 'padding:5px 5px 0',
			width : 350,
			defaults : {
				width : 100
			},
			defaultType : 'textfield',

			items : [{
						fieldLabel : il8n.user.username,
						name : 'username',
						allowBlank : false
					}, {
						fieldLabel : il8n.user.password,
						name : 'password',
						inputType : 'password',
						allowBlank : false
					}, {
						fieldLabel : il8n.normal.CAPTCHA,
						name : 'CAPTCHA',
						allowBlank : false,
						id : 'CAPTCHA_reg'
					}, new Ext.BoxComponent({
						//验证码部分
						fieldLabel : il8n.normal.CAPTCHA,
						height : 32,
						autoEl : {
							tag : 'div',
							html : '<img style="width:100px; height:28px;" id="captcha_reg" src="'
									+ thisObj.config.libPath
									+ 'securimage/securimage_show.php" alt="CAPTCHA Image" />'
						}
					})],

			buttons : [{
				text : il8n.user.register,
				handler : function() {
					var obj = form.getForm().getValues();
					Ext.Ajax.request({
								method : 'POST',
								url : thisObj.config.AJAXPATH
										+ "?controller=user&action=add",
								success : function(response) {
									var obj = jQuery.parseJSON(response.responseText);
									if (obj.msg == 'success') {
										alert(il8n.normal.success);
										win.close();
									}else{
										$.blockUI({
													message : '<h1>' + il8n.noraml.fail + '</h1>'
												});
										setTimeout($.unblockUI, 2000);
										$('#captcha_reg').attr(
													"src",
													thisObj.config.libPath
															+ 'securimage/securimage_show.php?wlstemp='
															+ Math.random());
									}
								},
								failure : function(response) {
									alert(il8n.normal.connectinoError);
								},
								params : obj
							});
				}
			}, {
				text : il8n.normal.CAPTCHA,
				handler : function() {
					$('#captcha_reg').attr(
							"src",
							thisObj.config.libPath + 'securimage/securimage_show.php?wlstemp='
									+ Math.random());
				}
			}]
		});

		var win = new Ext.Window({
					title : il8n.user.register,
					width : 250,
					height : 200,
					layout : 'fit',
					plain : true,
					bodyStyle : 'padding:5px;',
					buttonAlign : 'center',
					items : [form],
					modal : true
				});

		win.show();
	},
	
	/**
	 * 执行登录操作
	 * 之所以将这个函数与 登录界面 函数分离
	 * 是因为用户会经常在其他地方执行登录操作,花样繁多
	 * */
	login : function() {
		var thisObj = this;
		var form = Ext.getCmp('wls_melogin_form').getForm();

		if (form.isValid()) {
			var msg = new Ext.Window({
				html:'<span id="userLoginMsg">'+il8n.normal.submitting+'</span>',
				height:150,width:'80%'
			});
			msg.show();
			var obj = form.getValues();
			Ext.Ajax.request({
				method : 'POST',
				url : thisObj.config.AJAXPATH
						+ "?controller=user&action=login&temp=" + Math.random(),
				success : function(response) {
					
					var obj = jQuery.parseJSON(response.responseText);
					if (obj.state == 'success') {
						Ext.getDom('userLoginMsg').innerHTML = obj.msg;
						parent.location.reload();
					}else{
						Ext.getDom('userLoginMsg').innerHTML = obj.msg;
						$('#captcha').attr(
								"src",
								thisObj.config.libPath + 'securimage/'
										+ 'securimage_show.php?wlstemp='
										+ Math.random());
					}
				},
				failure : function(response) {
					Ext.Msg.alert('failure', response.responseText);
					$('#captcha').attr(
							"src",
							thisObj.config.libPath + 'securimage/'
									+ 'securimage_show.php?wlstemp='
									+ Math.random());
				},
				params : obj
			});
		} else {
			Ext.Msg.alert(il8n.normal.fail, il8n.normal.RequesttedImputMissing);
		}
	},
	
	/***
	 * 退出系统,就是执行页面跳转
	 * 就是直接访问一个清空SESSSION的页面,
	 * */
	logOut : function() {
		window.location.href = this.config.AJAXPATH
				+ "?controller=user&action=logout";
	},
	
	/**
	 * 得到所有的用户列表
	 * 这个列表操作一般都是管理员执行的,所以这个列表是一个 可编辑 列表
	 * 
	 * @return Ext.grid.EditorGridPanel
	 * */
	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=user&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'username', 'password', 'money', 'credits']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.user.username,
								dataIndex : 'username'
							}, {
								header : il8n.user.password,
								dataIndex : 'password',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.user.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.user.credits,
								dataIndex : 'credits',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}]
				});
				
		var bbar = new Ext.PagingToolbar({
						store : store,
						pageSize : 15,
						displayInfo : true
					});
		var tb = new Ext.Toolbar({
			id : "w_s_l_tb" + domid,items:[
			{
				iconCls: 'x-tbar-loading',
				tooltip : il8n.user.showAllColumns,
				handler : function() {
					Ext.Ajax.request({
						method : 'GET',
						url : thisObj.config.AJAXPATH
								+ "?controller=user&action=getColumns",
						success : function(response) {
							var obj = jQuery.parseJSON(response.responseText);

							var columns = [{
								header : il8n.user.username,
								dataIndex : 'username'
							}, {
								header : il8n.user.password,
								dataIndex : 'password',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.user.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.user.credits,
								dataIndex : 'credits',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}];
							var fields = ['id', 'username', 'password', 'money', 'credits'];
							for(var i=0;i<obj.length;i++){
								fields.push('column'+(obj[i].id-1));
								columns.push({
									header : obj[i].title,
									dataIndex : 'column'+(obj[i].id-1),
									editor : new Ext.form.TextField()
								});
							}
							store = new Ext.data.JsonStore({
								autoDestroy : true,
								url : thisObj.config.AJAXPATH
										+ '?controller=user&action=getList',
								root : 'data',
								idProperty : 'id',
								fields : fields
							});
							
							cm = new Ext.grid.ColumnModel({
									defaults : {
										sortable : true
									},
									columns : columns
								});
							Ext.getCmp(domid).reconfigure(store,cm);
							bbar.bind(store);
							store.load({
								params : {
									start : 0,
									limit : 15
								}
							});

						},
						failure : function(response) {
							Ext.Msg.alert('failure', response.responseText);
						}
					});
				}
			}
			]
		});
		var grid = new Ext.grid.EditorGridPanel({
			store : store,
			cm : cm,
			id : domid,
			width:'100%',
			height:500,
			clicksToEdit : 2,
			loadMask : true,
			tbar : tb,
			
			bbar : bbar
		});

		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=user&action=getCurrentUserSession",
			success : function(response) {
				var obj = Ext.decode(response.responseText);

				var access = obj.access;
				//以下的一系列权限参数都要跟配置文件中的 权限 一一对应
				//列表头部的那些权限操作按钮都是在这里一一添加的
				for(var i=0 ; i<access.length ; i++){
					if (access[i] == '1401') {
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_u_l_i',
									layout : 'fit',
									width : 500,
									height : 300,
									html : "<iframe src ='"
											+ thisObj.config.AJAXPATH
											+ "?controller=user&action=importAll' width='100%' height='250' />"
									});
									win.show();
								}
							});
					}else if(access[i] == '1402'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_u_l_i',
									layout : 'fit',
									width : 500,
									height : 300,
									html : "<iframe src ='"
											+ thisObj.config.AJAXPATH
											+ "?controller=user&action=exportAll' width='100%' height='250' />"
									});
									win.show();
								}
							});
					}else if(access[i] == '1403'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
										+ "?controller=user&action=delete",
									success : function(response) {
										store.load();
									},
									failure : function(response) {
										Ext.Msg.alert('failure', response.responseText);
									},
									params : {
										id : Ext.getCmp(domid).getSelectionModel().selection.record.id
									}
								});
							}
						});
					}else if(access[i] == '1404'){
						grid.on("afteredit",function(e){	
							Ext.Ajax.request({
								method : 'POST',
								url : thisObj.config.AJAXPATH
										+ "?controller=user&action=saveUpdate",
								success : function(response) {
									// Ext.Msg.alert('success',response.responseText);
								},
								failure : function(response) {
									Ext.Msg.alert('failure', response.responseText);
								},
								params : {
									field : e.field,
									value : e.value,
									id : e.record.data.id
								}
							});
						});
					}else if(access[i] == '1405'){
					//}else if(1=1){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								thisObj.register();
							}
						});
					}else if(access[i] == '1406'){
					//}else if(1=1){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
								var tree = new Ext.tree.TreePanel({
									id : 'u_l_g_t',
									height : 300,
									width : 400,
									useArrows : true,
									autoScroll : true,
									animate : true,
									enableDD : false,
									containerScroll : true,
									rootVisible : false,
									frame : true,
									root : {
										nodeType : 'async',
										expanded : true
									},
			
									dataUrl : thisObj.config.AJAXPATH
											+ "?controller=user&action=getGroupTree&username="
											+ username,
									buttons : [{
										text : il8n.submit,
										handler : function() {
											var checkedNodes = tree.getChecked();
											var s = "";
											for (var i = 0; i < checkedNodes.length; i++) {
												s += checkedNodes[i].attributes.id_level
														+ ",";
											}
											Ext.getCmp("u_l_g_t").setVisible(false);
			
											Ext.Ajax.request({
												method : 'POST',
												url : thisObj.config.AJAXPATH + "?controller=user&action=updateGroup",
												success : function(response) {
													Ext.getCmp("w_u_l_g_w").close();
												},
												failure : function(response) {
													Ext.Msg.alert('failure',response.responseText);
													Ext.getCmp("w_u_l_g_w").close();
												},
												params : {
													username : username,
													accesss : s.substring(0, s.length- 1)
												}
											});
										}
									}]	
								});
			
								var win = new Ext.Window({
											id : 'w_u_l_g_w',
											layout : 'fit',
											title : username + " " + il8n.UserToGroup,
											width : 500,
											height : 300,
											modal : true,
											items : [tree]
										});
								win.show(this);
							}
						});
					}else if(access[i] == '1407'){
					//}else if(1=1){				
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
								var tree = new Ext.tree.TreePanel({
									id : 'w_u_l_p_t',
									height : 300,
									width : 400,
									useArrows : true,
									autoScroll : true,
									animate : true,
									enableDD : false,
									containerScroll : true,
									rootVisible : false,
									frame : true,
									root : {
										nodeType : 'async',
										expanded : true
									},
			
									dataUrl : thisObj.config.AJAXPATH
											+ "?controller=user&action=getAccessTree&username="
											+ username
			
								});
			
								var win = new Ext.Window({
											id : 'w_u_l_p_w',
											layout : 'fit',
											title : username + " " + il8n.access,
											width : 500,
											height : 300,
											modal : true,
											items : [tree]
										});
								win.show(this);
							}
						});
					}else if(access[i] == '1408'){
					//}else if(1=1){		
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
								var tree = new Ext.tree.TreePanel({
									id : 'w_u_l_s_t',
									height : 300,
									width : 400,
									useArrows : true,
									autoScroll : true,
									animate : true,
									enableDD : false,
									containerScroll : true,
									rootVisible : false,
									frame : true,
									root : {
										nodeType : 'async',
										expanded : true
									},
									dataUrl : thisObj.config.AJAXPATH
											+ "?controller=user&action=getSubjectTree&username="
											+ username
			
								});
			
								var win = new Ext.Window({
											id : 'w_u_l_s_w',
											layout : 'fit',
											title : username + " " + il8n.subject,
											width : 500,
											height : 300,
											modal : true,
											items : [tree]
										});
								win.show();
							}
						});
					}else if(access[i] == '1409'){
					//}else if(1=1){		
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								Ext.Ajax.request({
									method : 'GET',
									url : thisObj.config.AJAXPATH + "?controller=user&action=cleanCache",
									success : function(response) {
										alert(il8n.noraml.success);
									},
									failure : function(response) {
										Ext.Msg.alert('failure',response.responseText);
										
									}
								});
							}
						});
					}
				}
				tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});
		
		store.load({
					params : {
						start : 0,
						limit : 15
					}
				});
		return grid;
	},
	
	/**
	 * 得到我的个人统计中心
	 * 主要显示 科目知识点掌握度
	 * TODO 等待用户反映
	 * */
	getMyCenter : function(domid) {
		var border = new Ext.Panel({
			id : domid,
			layout : 'border',
			items : [new Ext.Panel({
								region : 'east',
								width : 200,
								html : '<div id="' + domid
										+ '_subjects"></div>'
							}), new Ext.Panel({
						region : 'center'

						,
						html : il8n.statistic
								+ ':<div id="chart1"></div><table width="100%" height="200px"><tr><td><div id="'
								+ domid
								+ '_uc"></div></td><td width="50%">知识点掌握:<br/>'
								+ '<div id="chart2"><strong>You need to upgrade your Flash Player</strong></div>'
								+ '</div></td></tr></table>'
					})

			]
		});
		return border;
	}
	
});