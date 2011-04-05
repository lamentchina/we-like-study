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
	 * Add a Login Window. If the user logged succesfully , the page woudl
	 * reload , To reget the user info.
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
						fieldLabel : il8n.username,
						width : 150,
						vtype : "alphanum",
						name : 'username',
						allowBlank : false
					}, {
						fieldLabel : il8n.password,
						width : 150,
						vtype : "alphanum",
						name : 'password',
						inputType : 'password',
						allowBlank : false
					}, {
						fieldLabel : il8n.CheckCAPTCHA,
						width : 150,
						vtype : "alphanum",
						enableKeyEvents : true,
						name : 'CAPTCHA',
						allowBlank : false,
						id : 'CAPTCHA'
					}, new Ext.BoxComponent({
						fieldLabel : il8n.CAPTCHA,
						height : 32, 
						autoEl : {
							tag : 'div',
							html : '<img style="width:100px; height:28px;" id="captcha" src="'
									+ thisObj.config.libPath
									+ 'securimage/securimage_show.php" alt="CAPTCHA Image" />'
						}
					})],

			buttons : [{
						text : il8n.Login,
						handler : function() {
							thisObj.login();
						}
					}, {
						text : il8n.Refresh + il8n.CAPTCHA,
						handler : function() {
							$('#captcha').attr(
									"src",
									thisObj.config.libPath + 'securimage/securimage_show.php?temp='
											+ Math.random());
						}
					}, {
						text : il8n.Register,
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
						fieldLabel : il8n.username,
						name : 'username',
						allowBlank : false
					}, {
						fieldLabel : il8n.password,
						name : 'password',
						inputType : 'password',
						allowBlank : false
					}, {
						fieldLabel : il8n.CheckCAPTCHA,
						name : 'CAPTCHA',
						allowBlank : false,
						id : 'CAPTCHA_reg'
					}, new Ext.BoxComponent({
						fieldLabel : il8n.CAPTCHA,
						height : 32,
						autoEl : {
							tag : 'div',
							html : '<img style="width:100px; height:28px;" id="captcha_reg" src="'
									+ thisObj.config.libPath
									+ 'securimage/securimage_show.php" alt="CAPTCHA Image" />'
						}
					})],

			buttons : [{
				text : il8n.Register,
				handler : function() {
					var obj = form.getForm().getValues();
					Ext.Ajax.request({
								method : 'POST',
								url : thisObj.config.AJAXPATH
										+ "?controller=user&action=add",
								success : function(response) {
									var obj = jQuery.parseJSON(response.responseText);
									if (obj.msg == 'success') {
										alert(il8n.success);
										win.close();
									}else{
										$.blockUI({
													message : '<h1>' + il8n.fail + '</h1>'
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

								},
								params : obj
							});
				}
			}, {
				text : il8n.CAPTCHA,
				handler : function() {
					$('#captcha_reg').attr(
							"src",
							thisObj.config.libPath + 'securimage/securimage_show.php?wlstemp='
									+ Math.random());
				}
			}]
		});


		var win = new Ext.Window({
					title : il8n.Register,
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
	login : function() {
		var thisObj = this;
		var form = Ext.getCmp('wls_melogin_form').getForm();

		if (form.isValid()) {
			//$.blockUI({message : '<h1>' + il8n.loading + '......</h1>'});
			var msg = new Ext.Window({
				html:'<span id="userLoginMsg">'+il8n.loading+'</span>',
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
						//$.blockUI({message : '<h1>' + obj.msg + '</h1>'});
						//setTimeout($.unblockUI, 2000);

						$('#captcha').attr(
								"src",
								thisObj.config.libPath + 'securimage/'
										+ 'securimage_show.php?wlstemp='
										+ Math.random());
					}
				},
				failure : function(response) {
					$.unblockUI();
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
			Ext.Msg.alert(il8n.fail, il8n.RequesttedImputMissing);
		}
	},
	logOut : function() {
		window.location.href = this.config.AJAXPATH
				+ "?controller=user&action=logout";
	},
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
								header : il8n.username,
								dataIndex : 'username'
							}, {
								header : il8n.password,
								dataIndex : 'password',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.credits,
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
				tooltip : il8n.showAllColumns,
				handler : function() {
					Ext.Ajax.request({
						method : 'GET',
						url : thisObj.config.AJAXPATH
								+ "?controller=user&action=getColumns",
						success : function(response) {
							var obj = jQuery.parseJSON(response.responseText);

							var columns = [{
								header : il8n.username,
								dataIndex : 'username'
							}, {
								header : il8n.password,
								dataIndex : 'password',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.credits,
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

		var access = me.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '1401') {
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
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
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
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
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
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
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						thisObj.register();
					}
				});
			}else if(access[i] == '1406'){
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
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
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
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
				eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
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
			}
		}
		
		store.load({
					params : {
						start : 0,
						limit : 15
					}
				});
		return grid;
	},
	
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
	},
	afterMyCenterAdded : function(domid) {
		var str = "<div><img style='border:2px;' src='" + this.config.filePath
				+ 'images/user/' + me.myUser.photo
				+ "' height='200' width='150' /></div>" + "<div>"
				+ me.myUser.money + il8n.money + "</div>" + "<div>:"
				+ il8n.subjectsCountJoined
				+ me.myUser.subject.split(',').length + "</div>" + "<div>:"
				+ il8n.accesssCountJoined
				+ me.myUser.access.split(',').length + "</div>";
		$('#' + domid + '_uc').append(str);
		this.getMySubjectList(domid + '_subjects');
		var so = new SWFObject(this.config.libPath + "am/amradar/amradar.swf",
				me.myUser.id + "amradar", "320", "300", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath + "am/amradar/");
		so.addVariable("chart_id", me.myUser.id + "amradar");
		so.addVariable("settings_file",encodeURIComponent(this.config.AJAXPATH
								+ "?controller=knowledge_log&action=getMyRaderSetting"));
		so.write("chart2");

		var so = new SWFObject(this.config.libPath + "am/amline/amline.swf",
				me.myUser.id + "amline", "100%", "200", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath + "am/amline/");
		so.addVariable("chart_id", me.myUser.id + "amline");
		so.addVariable("settings_file", encodeURIComponent(this.config.AJAXPATH
						+ "?controller=subject&action=getMyQuizLine"));
		so.write("chart1");
	}
	,
	getMySubjectList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=user&action=getSubject&username='
							+ me.myUser.username,
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'name', 'id_level']
				});
		store.load({
				params : {
					start : 0,
					limit : 50
				}
			});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.name,
								dataIndex : 'name',
								width : 150
							}, {
								header : il8n.id_level,
								dataIndex : 'id_level',
								hidden : true
							}]
				});
		var grid = new Ext.grid.GridPanel({
					store : store,
					frame : true,
					title : il8n.mySubjects,
					cm : cm,
					renderTo : domid,
					// width: '90%',
					height : 500,
					loadMask : true,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 50,
								displayInfo : true
							})
				});
		grid.addListener('rowclick', function(t, r, e) {
			var id_s = t.store.data.items[r].data.id_level;
			var obj1 = document.getElementById(me.myUser.id + "amline");
			obj1.reloadSettings(thisObj.config.AJAXPATH
							+ "?controller=subject&action=getMyQuizLine&id_level_subject_="
							+ id_s);

			var obj2 = document.getElementById(me.myUser.id + "amradar");
			obj2.reloadSettings(thisObj.config.AJAXPATH
					+ "?controller=knowledge_log&action=getMyRaderSetting&id="
					+ id_s);
		});
	}
});