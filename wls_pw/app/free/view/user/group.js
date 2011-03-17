wls.user.group = Ext.extend(wls, {
	getAddItemForm : function() {
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id : 'w_u_g_ai_f',
			labelWidth : 75,
			frame : true,
			bodyStyle : 'padding:5px 5px 0',
			width : 350,
			defaults : {
				width : 100
			},
			defaultType : 'textfield',

			items : [{
						fieldLabel : il8n.id_level,
						width : 150,
						vtype : "alphanum",
						name : 'id_level',
						allowBlank : false
					}, {
						fieldLabel : il8n.name,
						width : 150,
						name : 'name',
						allowBlank : false
					}],

			buttons : [{
				text : il8n.save,
				handler : function() {
					var form = Ext.getCmp('w_u_g_ai_f').getForm();

					if (form.isValid()) {
						$.blockUI({
									message : '<h1>' + il8n.loading
											+ '......</h1>'
								});
						var obj = form.getValues();
						Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH
									+ "?controller=user_group&action=add&temp="
									+ Math.random(),
							success : function(response) {
								var obj = jQuery
										.parseJSON(response.responseText);
								$.unblockUI();
							},
							failure : function(response) {
								// TODO
								$.unblockUI();
							},
							params : obj
						});
					} else {
						Ext.Msg.alert(il8n.fail, il8n.RequesttedImputMissing);
					}
				}
			}]
		});
		return form;
	}
	,
	getTreeGrid:function(domid){
		var thisObj = this;
		var record = Ext.data.Record.create([
		{name: 'name'},
		{name: 'count_user'},	
		{name: 'id_level',type:'string'},
		{name: 'id',type:'int'},
		{name: '_parent', type: 'auto'},
		{name: '_is_leaf', type: 'bool'}
		]);
		
		var store = new Ext.ux.maximgb.tg.AdjacencyListStore({
		 	autoLoad : true,
		 	url: thisObj.config.AJAXPATH
				+ "?controller=user_group&action=getTreeGrid&temp="
				+ Math.random(),
			reader: new Ext.data.JsonReader({
				id: 'id',
				root: 'data',
				totalProperty: 'total',
				successProperty: 'success'
			}, 
			record)
		});
		var tb = new Ext.Toolbar({
					id : "w_s_l_tb" + domid
				});
		var grid = new Ext.ux.maximgb.tg.EditorGridPanel({
		   store: store,
		   id : domid,
		   tbar : tb,
		   master_column_id : 'name',
		   columns: [
		   	{
		   		 id:'name'
		   		,header: il8n.name
		   		,width: 75
		   		,dataIndex: 'name'
		   		,editor: new Ext.form.TextField()
		   		,renderer : function(v, meta, record, row_idx, col_idx, store){
               		return v;
            	}
			},{
			    header: il8n.id_level, 
				dataIndex: 'id_level',
				editor: new Ext.form.TextField()
			},{
			    header: il8n.count_user, 
				dataIndex: 'count_user'
			}],
			autoExpandColumn: 'name'
		});

		var access = user_.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '1301') {
				tb.add(this.importFile(access[i]));
			}else if (access[i] == '1302') {
				tb.add(this.exportFile(access[i]));
			}  else if (access[i] == '130401') {
				eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = user_.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
						thisObj.getAccessTree(id);

					}
				});
			} else if (access[i] == '130402') {
				eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = user_.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
						thisObj.getSubjectTree(id);
					}
				});
			} else if (access[i] == '1305') {
				eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = user_.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						var form = thisObj.getAddItemForm();
						var w = new Ext.Window({
									title : il8n.add,
									width : 350,
									height : 300,
									layout : 'fit',
									buttonAlign : 'center',
									items : [form],
									modal : true
								});

						w.show();
					}
				});
			} else if (access[i] == '1304') {
				grid.on("afteredit",function(e){
					//console.debug(e);return;
					Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=saveUpdate",
						success : function(response) {
							var msg = jQuery.parseJSON(response.responseText);
							QoDesk.App.getDesktop().showNotification({
								html :  msg.msg,
								title : il8n.success
							});
						},
						failure : function(response) {
							Ext.Msg.alert('failure', response.responseText);
						},
						params : {
							field : e.field,
							value : e.value,
							originalValue : e.originalValue,
							id : e.record.data.id
						}
					});
				});
			} else if (access[i] == '1303') {
				eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = user_.myUser.access2.p"+access[i]+"[2];");
				tb.add( {
					iconCls: iconCls,
					tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
							return;
						}
						Ext.MessageBox.confirm( Ext.MessageBox.buttonText.ok+'?', il8n.sureToDelete+"?<br/>"+il8n.cascadingDelete, function(button,text){  
               				if(button=='yes'){
               					Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=user_group&action=delete",
									success : function(response) {
										var msg = jQuery.parseJSON(response.responseText);
										QoDesk.App.getDesktop().showNotification({
											html :  msg.msg,
											title : il8n.success
										});
									},
									failure : function(response) {
										Ext.Msg.alert('failure',response.responseText);
									},
									params : {
										id : Ext.getCmp(domid).getSelectionModel().selection.record.id
									}
								});
               				}
            			});
					}
				});
			}
		}
		return grid;
	}
	,importFile:function(access){
		var thisObj = this;
		eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access+"[1]+'_16_16';");
		eval("var tooltip = user_.myUser.access2.p"+access+"[2];");
		return {
			iconCls: iconCls,
			tooltip : tooltip,
			handler : function() {
				var win = new Ext.Window({
					id : 'w_u_g_l_i',
					layout : 'fit',
					width : 500,
					height : 300,
					html : "<iframe src ='"
							+ thisObj.config.AJAXPATH
							+ "?controller=user_group&action=importAll' width='100%' height='250' />"
				});
				win.show(this);
			}
		}
	}
	,exportFile:function(access){
		var thisObj = this;
		eval("var iconCls = 'bt_'+user_.myUser.access2.p"+access+"[1]+'_16_16';");
		eval("var tooltip = user_.myUser.access2.p"+access+"[2];");
		return {
			iconCls: iconCls,
			tooltip : tooltip,
			handler : function() {
				var win = new Ext.Window({
					id : 'w_u_g_l_e',
					layout : 'fit',
					width : 500,
					height : 300,
					html : "<iframe src ='"
							+ thisObj.config.AJAXPATH
							+ "?controller=user_group&action=exportAll' width='100%' height='250' />"
				});
				win.show(this);
			}
		}
	}
	,getAccessTree:function(id){
		var thisObj = this;
		var tree = new Ext.tree.TreePanel({
			id : 'w_u_g_l_p_t',
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
					+ "?controller=user_group&action=getAccessTree&id="
					+ id,
			buttons : [{
				text : il8n.submit,
				handler : function() {
					var checkedNodes = tree.getChecked();
					var s = "";
					for (var i = 0; i < checkedNodes.length; i++) {
						s += checkedNodes[i].attributes.id_level
								+ ",";
					}
					Ext.getCmp("w_u_g_l_p_t").setVisible(false);
					Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=saveAccessTree",
						success : function(response) {
							Ext.getCmp("w_u_g_l_p_w").close();
						},
						failure : function(response) {
							Ext.Msg.alert('failure',
									response.responseText);
						},
						params : {
							id : id,
							ids : s.substring(0, s.length - 1)
						}
					});
				}
			}]
		});

		var win = new Ext.Window({
					id : 'w_u_g_l_p_w',
					layout : 'fit',
					title : il8n.access,
					width : 500,
					height : 300,
					modal : true,
					items : [tree]
				});
		win.show(this);
	}
	,getSubjectTree:function(id){
		var thisObj = this;
		var tree = new Ext.tree.TreePanel({
			id : 'w_u_g_l_s_t',
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
					+ "?controller=user_group&action=getSubjectTree&id="
					+ id,
			buttons : [{
				text : il8n.submit,
				handler : function() {
					var checkedNodes = tree.getChecked();
					var s = "";
					for (var i = 0; i < checkedNodes.length; i++) {
						s += checkedNodes[i].attributes.id_level + ",";
					}
					Ext.getCmp("w_u_g_l_s_t").setVisible(false);
					Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=saveSubjectTree",
						success : function(response) {
							Ext.getCmp("w_u_g_l_s_w").close();
						},
						failure : function(response) {
							Ext.Msg.alert('failure',
									response.responseText);
						},
						params : {
							id : id,
							ids : s.substring(0, s.length - 1)
						}
					});
				}
			}]

		});

		var win = new Ext.Window({
					id : 'w_u_g_l_s_w',
					layout : 'fit',
					title : il8n.subject,
					width : 500,
					height : 300,
					modal : true,
					items : [tree]
				});
		win.show(this);
	}
});
