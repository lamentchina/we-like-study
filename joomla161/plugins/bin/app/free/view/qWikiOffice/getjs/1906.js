class_1906 = Ext.extend(Ext.app.Module, {
	id : 'id_1906',
	init : function() {

	},
	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);


		if (!win) {
			var winWidth = 450;
			var winHeight = 260;

			win = desktop.createWindow({
				id : this.id,
				title : il8n.systemSettings,
				width : winWidth,
				height : winHeight,				
				iconCls : 'icon_server_16_16',
				iconClsGhostBar : 'icon_server_32_32',
				modal : true,
				layout : 'fit',
				html : "<iframe src ='"
					+ me.config.AJAXPATH
					+ "?controller=system&action=viewModifySystemSettings' width='100%' height='230' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
				
			});
		}
		win.show();
	}
});