class_15 = Ext.extend(Ext.app.Module, {
			id : 'id_15',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var winWidth = desktop.getWinWidth() / 1.1;
				var winHeight = 430;



				if (!win) {
					win = desktop.createWindow({
								id : this.id,
								title : il8n.access,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_access_16_16',
								iconClsGhostBar : 'icon_access_32_32',
								layout : 'fit',
								html : "<iframe src ='"
									+ me.config.AJAXPATH
									+ "?controller=user_access&action=viewGetList' width='100%' height='430' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
				}
				win.show();
			}
		});