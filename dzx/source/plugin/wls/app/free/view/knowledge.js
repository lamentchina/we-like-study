wls.knowledge = Ext.extend(wls, {

	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=knowledge&action=jsonList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'id_level', 'name', 'weight', 'description']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.id,
								dataIndex : 'id_level'
							}, {
								header : il8n.name,
								dataIndex : 'name',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.weight,
								dataIndex : 'weight',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.description,
								dataIndex : 'description',
								hidden : true,
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_s_l_tb"
				});

		var grid = new Ext.grid.EditorGridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : 600,
					height : 300,
					clicksToEdit : 1,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		var privilege = user_.myUser.privilege.split(",");
		for (var i = 0; i < privilege.length; i++) {
			if (privilege[i] == '190801') {
				tb.add({
					text : il8n.importFile,
					handler : function() {
						var win = new Ext.Window({
							id : 'w_u_g_l_i',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=knowledge&action=viewUpload' width='19080%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
						});
						win.show(this);
					}
				});
			} else if (privilege[i] == '190802') {
				tb.add({
					text : il8n.exportFile,
					handler : function() {
						var win = new Ext.Window({
							id : 'w_u_g_l_e',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=knowledge&action=viewExport' width='19080%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
						});
						win.show(this);
					}
				});
			} else if (privilege[i] == '190803') {
				tb.add({
					text : il8n.deleteItems,
					handler : function() {
						Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH
									+ "?controller=knowledge&action=delete",
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
			} else if (privilege[i] == '190804') {
				grid.on("afteredit", afteredit, grid);
			} else if (privilege[i] == '190805') {
				// TODO
			}
			function afteredit(e) {
				Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH
									+ "?controller=knowledge&action=saveUpdate",
							success : function(response) {
								// TODO
							},
							failure : function(response) {
								// TODO
							},
							params : {
								field : e.field,
								value : e.value,
								id : e.record.data.id
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
	}
});
