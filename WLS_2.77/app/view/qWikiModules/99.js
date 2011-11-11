class_99 = Ext.extend(Ext.app.Module, {
	id : 'id_99',

	init : function() {

	},

	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);
		var title = '';
		for(var i=0;i<modules.length;i++){
			if(modules[i].id=='id_99'){
				title = modules[i].launcher.text;
			}
		}
		
		if (!win) {
			win = desktop.createWindow({
				id : this.id,
				title : title,
				width : 400,
				height : 350,
				iconCls : 'icon_about_16_16',
				iconClsGhostBar : 'icon_about_32_32',
				shim : false,
				constrainHeader : true,
				layout : 'fit',
				modal : true,
				html : "<iframe src ='' width='100%' height='350' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
			});
		}
		win.show();				
	}
});