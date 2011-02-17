class_1906 = Ext.extend(Ext.app.Module, {
	id : 'id_1906',
	init : function() {

	},
	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);
		var obj = new wls.user();

		if (!win) {
			var winWidth = 300;
			var winHeight = 200;

			win = desktop.createWindow({
				id : this.id,
				title : il8n.systemSettings,
				width : winWidth,
				height : winHeight,
				modal : true,
				layout : 'fit',
				items : [obj.modifySystemSettings()]
				,
			});
		}
		win.show();
	}
});