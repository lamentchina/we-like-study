class_13 = Ext.extend(Ext.app.Module, {
			id : 'id_13',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var winWidth = 550;
				var winHeight = 300;

				var obj = new wls.user.group();

				if (!win) {
					win = desktop.createWindow({
								id : this.id,
								title : il8n.usergroup,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_group_16_16',
								iconClsGhostBar : 'icon_group_32_32',
								layout : 'fit',
								items : [obj.getTreeGrid('qd_w_u_g_l')]
							});
				}
				win.show();
			}
		});