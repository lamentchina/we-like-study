class_19 = Ext.extend(Ext.app.Module, {
		id : 'id_19',

		init : function() {

		},

		createWindow : function() {
			var desktop = this.app.getDesktop();
			var win = desktop.getWindow(this.id);
			var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = 460;

			if (!win) {
				win = desktop.createWindow({
							id : this.id,
							title : il8n.teacher,
							width : winWidth,
							height : winHeight,
							iconCls : 'icon_teacher_16_16',
							iconClsGhostBar : 'icon_teacher_32_32',
							layout : 'fit',
							html : "<iframe src ='user/teacher/grid.html' width='100%' height='430' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							//items : [obj.getList('wls_teacher')]
						});
			}
			win.show();
		}
	});