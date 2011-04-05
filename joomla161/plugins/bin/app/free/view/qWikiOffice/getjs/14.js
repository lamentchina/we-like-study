class_14 = Ext.extend(Ext.app.Module, {
			id : 'id_14',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var winWidth = 550;
				var winHeight = 530;

				if (!win) {
					win = desktop.createWindow({
								id : this.id,
								title : il8n.user,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_user_16_16',
								iconClsGhostBar : 'icon_user_32_32',
								layout : 'fit',
								html : "<iframe src ='"
									+ me.config.AJAXPATH
									+ "?controller=user&action=viewGetList' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
				}
				win.show();
			}
		});