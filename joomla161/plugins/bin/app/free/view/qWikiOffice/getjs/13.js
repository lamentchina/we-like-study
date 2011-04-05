class_13 = Ext.extend(Ext.app.Module, {
			id : 'id_13',

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
								title : il8n.usergroup,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_group_16_16',
								iconClsGhostBar : 'icon_group_32_32',
								layout : 'fit',
								html : "<iframe src ='"
									+ me.config.AJAXPATH
									+ "?controller=user_group&action=viewGetTreeGrid' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
				}
				win.show();
			}
		});