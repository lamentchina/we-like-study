wls.user.access = Ext.extend(wls, {
	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=user_access&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'id_level', 'name', 'money', 'ismenu',
							'icon', 'isshortcut', 'isquickstart','description']
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
								header : il8n.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.ismenu,
								dataIndex : 'ismenu'

							}, {
								header : il8n.isshortcut,
								dataIndex : 'isshortcut',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.isquickstart,
								dataIndex : 'isquickstart',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.icon,
								dataIndex : 'icon',
								editor : new Ext.form.TextField()
							}, {
								 header : il8n.description
								,dataIndex : 'description'
								,editor : new Ext.form.TextField()
								,hidden:true
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_u_p_l_tb",
					items : []
				});

		var grid = new Ext.grid.EditorGridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : "100%",
					height : 400,
					clicksToEdit : 2,
					loadMask : true,
					tbar : tb,

					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});
		grid.on("afteredit", afteredit, grid);
		function afteredit(e) {
			Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=user_access&action=saveUpdate",
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
						limit : 15
					}
				});

		if (typeof(me) == "undefined") {
			//
		} else {
			var access = me.myUser.access.split(",");

			for (var i = 0; i < access.length; i++) {
				if (access[i] == '190701') {
					eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
					tb.add('-', {
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							var win = new Ext.Window({
								title : il8n.importFile,
								id : 'w_u_p_l_i',
								layout : 'fit',
								width : 500,
								modal : true,
								height : 300,
								html : "<iframe src ='"
										+ thisObj.config.AJAXPATH
										+ "?controller=user_access&action=importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (access[i] == '190702') {
					eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
					tb.add('-', {
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							var win = new Ext.Window({
								title : il8n.exportFile,
								id : 'w_u_p_l_e',
								layout : 'fit',
								width : 500,
								modal : true,
								height : 300,
								html : "<iframe src ='"
										+ thisObj.config.AJAXPATH
										+ "?controller=user_access&action=exportAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (access[i] == '190703') {
					// TODO
				}
			}
		}

		return grid;
	}
});
