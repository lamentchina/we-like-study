class_23 = Ext.extend(Ext.app.Module, {
	id : 'id_23',
	init : function() {},
	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);
	
		if (!win) {
			win = desktop.createWindow({
				id : this.id,
				title : il8n.Login,
				width : 300,
				height : 300,
				iconCls : 'icon_key_16_16',
				iconClsGhostBar : 'icon_key_32_32',
				shim : false,
				constrainHeader : true,
				layout : 'fit',
				modal : true,
				html : "<iframe src ='user/login.html' width='100%' height='265' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
			});
		}
		win.show();
	}
});