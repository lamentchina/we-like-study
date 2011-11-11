class_1907 = Ext.extend(Ext.app.Module, {
			id : 'id_1907',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = 650;
					var winHeight = 430;
					

					win = desktop.createWindow({
								id : this.id,
								title : il8n.subject.subject,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_excel_16_16',
								iconClsGhostBar : 'icon_excel_32_32',
								layout : 'fit',
								html : "<iframe src ='subject/treeGrid.html' width='100%' height='400' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
								
							});
				}
				win.show();
			}
		});