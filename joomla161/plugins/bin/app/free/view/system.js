wls.system = Ext.extend(wls, {
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
						fieldLabel : il8n.setBackground,
						width : 150,
						name : 'background',
						allowBlank : false
					}, {
						fieldLabel : il8n.setTheme,
						width : 150,
						name : 'theme',
						allowBlank : false
					}, {
						fieldLabel : il8n.setSiteName,
						width : 150,
						name : 'siteName',
						allowBlank : false
					}],

			buttons : [{
				text : il8n.submit,
				handler : function() {
					Ext.Ajax.request({
						method : 'POST',
						params : form.getForm().getValues(),
						url : thisObj.config.AJAXPATH + "?controller=system&action=saveUpdate&temp=" + Math.random(),
						success : function(response) {
							alert(il8n.success);
							location.reload();
						},
						failure : function(response) {
							//TODO			
						}						
					});
				}
			},{
				text : il8n.importSysConfig,
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