class_99 = Ext.extend(Ext.app.Module, {
	id : 'id_99',

	init : function() {

	},

	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);
		var obj = new wls();
		
		if (!win) {
			win = desktop.createWindow({
				id : this.id,
				title : 'About Us',
				width : 400,
				height : 350,
				iconCls : 'icon_doquiz_16_16',
				shim : false,
				constrainHeader : true,
				layout : 'fit',
				modal : true,
				html : "<iframe src ='"
					+ obj.config.AJAXPATH
					+ "?controller=quiz&action=about' width='100%' height='350' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
			});
		}
		win.show();				
	}
});