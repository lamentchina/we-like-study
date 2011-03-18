class_15 = Ext.extend(Ext.app.Module, {
			id : 'id_15',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var winWidth = desktop.getWinWidth() / 1.1;
				var winHeight = desktop.getWinHeight() / 1.1;

				var obj = new wls.user.access();

				if (!win) {
					win = desktop.createWindow({
								id : this.id,
								title : il8n.access,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_access_16_16',
								iconClsGhostBar : 'icon_access_32_32',
								layout : 'fit',
								items : [obj.getList('qd_w_u_p_l')]
							});
				}
				win.show();
			}
		});