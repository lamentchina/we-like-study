class_20 = Ext.extend(Ext.app.Module, {
	id : 'id_20',

	init : function() {

	},

	createWindow : function() {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.id);

		var title = '';
		for(var i=0;i<qWikiSettings.modules.length;i++){
			if(qWikiSettings.modules[i].id=='id_20'){
				title = qWikiSettings.modules[i].launcher.text;
			}
		}
		if (!win) {
			win = desktop.createWindow({
				id : this.id,
				title : title,
				width : desktop.getWinWidth() / 1.1,
				height : 530,
				iconCls : 'icon_doquiz_16_16',
				iconClsGhostBar : 'icon_doquiz_32_32',
				shim : false,
				constrainHeader : true,
				layout : 'fit',
				listeners : {
					'show':function(x){
						var c = document.getElementById('examList');   
						c.src =  "../../wls.php?controller=quiz_exam&action=viewGetList";
					}
				},
				html : "<iframe id='examList' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />"		
			});
		}
		win.show();				
	}
});