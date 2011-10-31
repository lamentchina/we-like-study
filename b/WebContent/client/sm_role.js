sm.role= {
	list:function(){
		var store = new Ext.data.JsonStore( {
				autoDestroy : true,
				proxy : new Ext.data.HttpProxy( {
					url : url.sm_role_list,
					method : 'GET'
				}),
				root : 'data',
				idProperty : 'id',
				fields : [ 'id' , 'code' , 'name' , 'remark' ]
			});	
			
			var cm = new Ext.grid.ColumnModel( {
				defaults : {
					sortable : true
				},
				columns : [ 
					 { header : il8n.id , dataIndex : 'id' , hidden : true }
					,{ header : il8n.code , dataIndex : 'code' , width : 200 }
					,{ header : il8n.name , dataIndex : 'name' , width : 230 }
					,{ header : il8n.remark , dataIndex : 'remark' , width : 300 }
				 ]
			});
		
			var search = new Ext.form.TextField({
				id : 's_r_l_search',
				width : 200,
				enableKeyEvents : true
			});		
		
			search.on('keyup', function(a, b, c) {
				if (b.button == 12) {
					store.load({
						params : {
							start : 0,
							limit : 20,
							search : Ext.getCmp('s_r_l_search').getValue()
						}
					});
				}
			});
			
			var tb = new Ext.Toolbar({
				 id:'s_r_list_tb'
				,items : [ search, {
					text : il8n.search,
					handler : function() {
						store.load({
							params : {
								start : 0,
								limit : 20
							}
						});
					}
				},{
					text : il8n.advanced + il8n.search,
					handler : function() {
						//TODO
					}
				},'-']
			});
		
			store.on('beforeload', function() {
				Ext.apply(this.baseParams, {
					search : Ext.getCmp('s_r_l_search').getValue()
				});
			});
		
			var grid = new Ext.grid.GridPanel( {
				id : 's_r_list',
				store : store,
				cm : cm,
				height : 350,
				width : '95%',
				tbar : tb,
				loadMask : {  
					msg : il8n.loading
				} ,
				bbar : new Ext.PagingToolbar( {
					store : store,
					pageSize : 15,
					displayInfo : true
				})
			});
		
			store.load();
			return grid;
	},
	setToolBar:function(ToolBarID,Permissions){
		Ext.Ajax.request({
			method : 'POST',
			url : url.sm_role_mine,
			success : function(response) {
				var tb = Ext.getCmp(ToolBarID);
				var obj = Ext.decode(response.responseText);
				var access = obj.access;
				for (var i = 0; i < access.length; i++) {
					if (access[i].id == '100102') {
						tb.add({
							 text : il8n.detail
							,handler : function() {
								if (Ext.getCmp('s_r_list').getSelectionModel().selections.items.length == 0) {
									alert(il8n.error_rowSelectedFirst);
									return;
								}
								var id = Ext.getCmp('s_r_list').getSelectionModel().selections.items[0].data.id;
								var tree = sm.role.permissionsTree(id);
								var win = new Ext.Window({
									title:il8n.permissions,
									width:500,
									height:350,
									items:[tree]
								});
								win.show();
							}
						},'-');
						tb.add({
							 text : il8n.add
							,handler : function() {
								var form = sm.role.edit();
								var win = new Ext.Window({
									layout : 'fit',
									title : il8n.add,
									width : 500,
									height : 300,
									modal : true,
									items : [form]
								});
								win.show();
							}
						});
						tb.add({
							 text : il8n.remove
							,handler : function() {
								if (Ext.getCmp('s_r_list').getSelectionModel().selections.items.length == 0) {
									alert(il8n.error_rowSelectedFirst);
									return;
								}
								var id = Ext.getCmp('s_r_list').getSelectionModel().selections.items[0].data.id;
								
								Ext.MessageBox.confirm(il8n.RUsure,il8n.RUsure2remove,
									function(btn) {
										if (btn == 'yes') {
											Ext.Ajax.request({
												method : 'POST',
												url : url.sm_role_remove,
												success : function(response) {
													var obj = Ext.decode(response.responseText);
													if(obj.result=='success'){
														Ext.getCmp('s_r_list').getStore().load();
													}
												},
												failure : function(response) {
													alert(il8n.error_netConnectionFailed);
												},
												params : {'id':id}
											});
										}
									} 
								);  
							}
						});
					} else if (access[i].id == '10010202') {
						tb.add({
							 text : il8n.modify
							,handler : function() {
								if (Ext.getCmp('s_r_list').getSelectionModel().selections.items.length == 0) {
									alert(il8n.error_rowSelectedFirst);
									return;
								}
								var id = Ext.getCmp('s_r_list').getSelectionModel().selections.items[0].data.id;
								var tree = sm.role.permissionsTree4Edit(id);
								tree.title = il8n.permissions;
								var form = sm.role.edit(id);
								form.title = il8n.form;
								var win = new Ext.Window({
									id : 's_r_p_t_4e_w',
									layout : 'fit',
									title : il8n.permissions,
									
									width : 500,
									height : 300,
									modal : true,
									items : [new Ext.TabPanel({
										activeTab: 0,   
										items:[tree,form]
									})]
								});
								win.show();
							}
						});
					}
				}
				tb.doLayout();
			},
			failure : function(response) {
				alert(il8n.error_netConnectionFailed);
			},
			params : {'permissions':Permissions,'sid':sm.session.sid}
		});
	},
	permissionsTree:function(role_id){
		var tree = new Ext.ux.tree.TreeGrid({
			columns:[{
				header: il8n.name,
				dataIndex: 'name',
				width: 300
			},{
				header: il8n.code,
				width: 100,
				dataIndex: 'code',
				align: 'center'
			}],
			containerScroll : true,
			dataUrl: url.sm_role_permissionsTree+'&id='+role_id
		});
		return tree;
	},
	permissionsTree4Edit:function(id){
		var tree = new Ext.tree.TreePanel({
			id : 's_r_p_t_4e',
			
			useArrows : true,
			autoScroll : true,
			animate :false,
			enableDD : false,
			containerScroll : true,
			rootVisible : false,
			frame : true,
			root : {
				nodeType : 'async',
				expanded : false
			},

			dataUrl : url.sm_role_permissionsTree4Edit+'&id='+id,
			buttons : [{
				text : il8n.submit,
				handler : function() {
					var checkedNodes = tree.getChecked();
					var s = "";
					for (var i = 0; i < checkedNodes.length; i++) {
						s += checkedNodes[i].attributes.id + ",";
					}
					Ext.Ajax.request({
						method : 'POST',
						url : url.sm_role_permissions2save,
						success : function(response) {
							var obj = Ext.decode(response.responseText);
							if(obj.result=='success'){
								alert(il8n.operated);
								Ext.getCmp("s_r_p_t_4e_w").close();
							}
						},
						failure : function(response) {
							alert(il8n.error_netConnectionFailed);
						},
						params : {
							id : id,
							ids : s.substring(0, s.length - 1)
						}
					});
				}
			}]
		});
		return tree;
	},
	edit:function(id){
		if(typeof(id)=='undefined')id = 0;
		var form = new Ext.form.FormPanel({
			id : 's_r_add',
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
						fieldLabel : il8n.code,
						width : 150,
						vtype : "alphanum",
						name : 'username',
						allowBlank : false
					}, {
						fieldLabel : il8n.name,
						width : 150,
						vtype : "alphanum",
						name : 'password',
						allowBlank : false
					}, {
						fieldLabel : il8n.remark,
						width : 150,
						vtype : "alphanum",
						name : 'password',
						allowBlank : false
					}],
			buttons : [{
						text : il8n.submit,
						handler : function() {
							var form = Ext.getCmp('s_r_add').getForm();
							if (form.isValid()) {
								var obj = form.getValues();
								obj.id = id;
								Ext.Ajax.request({
									method : 'POST',
									url : url.sm_role_add,
									success : function(response) {
										var obj = Ext.decode(response.responseText);
										if(obj.result=='success'){
											alert(il8n.operated);
										}
									},
									failure : function(response) {
										alert(il8n.error_netConnectionFailed);
									},
									params : obj
								});
							}else{
								alert(il8n.error_checkTheFormInputs);
							}
						}
					}
			]
		});
		return form;
	}
}