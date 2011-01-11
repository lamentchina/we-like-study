wls.user.group = Ext.extend(wls, {
	getList : function(domid) {

		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=user_group&action=jsonList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'id_level', 'name', 'count_user']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.ID,
								dataIndex : 'id_level'
							}, {
								header : il8n.Name,
								dataIndex : 'name',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.Count.User,
								dataIndex : 'count_user',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}]
				});

		var grid = new Ext.grid.EditorGridPanel({
			store : store,
			cm : cm,
			id : domid,
			width : 600,
			height : 300,
			clicksToEdit : 2,
			loadMask:true,
			tbar : [{
				text : il8n.Import,
				handler : function() {
					var win = new Ext.Window({
						id : 'w_u_g_l_i',
						layout : 'fit',
						width : 500,
						height : 300,
						html : "<iframe src ='"
								+ thisObj.config.AJAXPATH
								+ "?controller=user_group&action=viewUpload' width='100%' height='250' />"
					});
					win.show(this);
				}
			}, {
				text : il8n.Export,
				handler : function() {
					var win = new Ext.Window({
						id : 'w_u_g_l_e',
						layout : 'fit',
						width : 500,
						height : 300,
						html : "<iframe src ='"
								+ thisObj.config.AJAXPATH
								+ "?controller=user_group&action=viewExport' width='100%' height='250' />"
					});
					win.show(this);
				}
			}, {
				text : il8n.Privilege,
				handler : function() {
					var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_g_l_p_t',
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

						// auto create TreeLoader
						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=getPrivilege&id="
								+ id,
						buttons : [{
							text : il8n.Submit,
							handler : function() {
								var checkedNodes = tree.getChecked();
								var s = "";
								for (var i = 0; i < checkedNodes.length; i++) {
									s += checkedNodes[i].attributes.id_level + ",";
								}
								Ext.getCmp("w_u_g_l_p_t").setVisible(false);
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=user_group&action=updatePrivilege",
									success : function(response) {
										Ext.getCmp("w_u_g_l_p_w").close();
									},
									failure : function(response) {
										Ext.Msg.alert('failure',
												response.responseText);
									},
									params : {
										 id : id
										,ids : s.substring(0,s.length-1)
									}
								});
							}
						}]

					});

					var win = new Ext.Window({
								id : 'w_u_g_l_p_w',
								layout : 'fit',
								title:il8n.Privilege,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
							});
					win.show(this);

				}
			}, {
				text : il8n.Subject,
				handler : function() {
					var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_g_l_s_t',
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

						// auto create TreeLoader
						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=getSubject&id="
								+ id,
						buttons : [{
							text : il8n.Submit,
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
											+ "?controller=user_group&action=updateSubject",
									success : function(response) {
										Ext.getCmp("w_u_g_l_s_w").close();
									},
									failure : function(response) {
										Ext.Msg.alert('failure',
												response.responseText);
									},
									params : {
										 id : id
										,ids : s.substring(0,s.length-1)
									}
								});
							}
						}]

					});

					var win = new Ext.Window({
								id : 'w_u_g_l_s_w',
								layout : 'fit',
								title:il8n.Subject,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
							});
					win.show(this);

				}
			}],
			bbar : new Ext.PagingToolbar({
						store : store,
						pageSize : 8,
						displayInfo : true
					})
		});
		grid.on("afteredit", afteredit, grid);
		function afteredit(e) {
			Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=saveUpdate",
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
		}
		store.load({
					params : {
						start : 0,
						limit : 8
					}
				});
		return grid;
	}
});
