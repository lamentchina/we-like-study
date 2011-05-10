class_1651 = Ext.extend(Ext.app.Module, {
			id : 'id_1651',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = desktop.getWinWidth() / 1.1;
					var winHeight = 530;


					win = desktop.createWindow({
								id : this.id,
								title : il8n.log_allQuiz,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_grid_16_16',
								iconClsGhostBar : 'icon_grid_32_32',
								layout : 'fit',
								html : "<iframe src ='"
									+ me.config.AJAXPATH
									+ "?controller=quiz_log&action=viewGetList' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
								
							});
				}
				win.show();
			}
		});