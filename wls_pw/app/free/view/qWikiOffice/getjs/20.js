class_20 = Ext.extend(Ext.app.Module, {
	id : 'id_20',

	init : function() {

	},

	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);
		var obj = new wls.quiz.exam();
		var title = '';
		for(var i=0;i<modules.length;i++){
			if(modules[i].id=='id_20'){
				title = modules[i].launcher.text;
			}
		}
		if (!win) {
			win = desktop.createWindow({
				id : this.id,
				title : title,
				width : desktop.getWinWidth() / 1.1,
				height : 350,
				iconCls : 'icon_doquiz_16_16',
				iconClsGhostBar : 'icon_doquiz_32_32',
				shim : false,
				constrainHeader : true,
				layout : 'fit',
				items : [obj.getList('qd_w_u_l')]				
			});
		}
		win.show();				
	}
});