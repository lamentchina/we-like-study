wls.system = Ext.extend(wls, {
	
	/**
	 * 修改系统配置参数
	 * 主要是修改 背景图 , 皮肤 , 站点名称等
	 * 
	 * TODO 应该考虑一下 缓存,数据库链接参数,数据库备份,配置文件重导入导出 等功能
	 * */
	modifySystemSettings : function() {
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id : 'wls_settings_form',
			labelWidth : 90,
			frame : true,
			width:'100%',
			height:230,

			defaults : {
				width : 100
			},
			defaultType : 'textfield',

			items : [{
						fieldLabel : il8n.system.setBackground,
						width : 150,
						name : 'background',
						allowBlank : false
					}, {
						fieldLabel : il8n.system.setTheme,
						width : 150,
						name : 'theme',
						allowBlank : false
					}, {
						fieldLabel : il8n.system.setSiteName,
						width : 150,
						name : 'siteName',
						allowBlank : false
					}],

			buttons : [{
				text : il8n.normal.submit,
				handler : function() {
					Ext.Ajax.request({
						method : 'POST',
						params : form.getForm().getValues(),
						url : thisObj.config.AJAXPATH + "?controller=system&action=saveUpdate&temp=" + Math.random(),
						success : function(response) {
							alert(il8n.normal.success);
							location.reload();
						},
						failure : function(response) {
							//TODO			
						}						
					});
				}
			},{
				text : il8n.system.importSysConfig,
				handler : function() {
					var win = new Ext.Window({
						id : 'w_s_ic',
						layout : 'fit',
						width : 400,
						height : 150,
						html : "<iframe src ='"
								+ thisObj.config.AJAXPATH
								+ "?controller=system&action=importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
					});
					win.show();
				}
			},{
				text : il8n.system.importSysConfig,
				handler : function() {
					var win = new Ext.Window({
						id : 'w_s_ic',
						layout : 'fit',
						width : 400,
						height : 150,
						html : "<iframe src ='"
								+ thisObj.config.AJAXPATH
								+ "?controller=system&action=importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
					});
					win.show();
				}
			}]
		});
		Ext.Ajax.request({
			method : 'GET',
			url : thisObj.config.AJAXPATH + "?controller=system&action=getConfig&temp=" + Math.random(),
			success : function(response) {
				var obj = jQuery.parseJSON(response.responseText);
				form.getForm().setValues(obj);
			},
			failure : function(response) {
				//TODO			
			}						
		});		
		return form;
	}	
});