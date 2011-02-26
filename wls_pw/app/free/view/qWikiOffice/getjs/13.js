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

								layout : 'fit',
								items : [obj.getList('qd_w_u_g_l')]
							});
				}
				win.show();
			}
		});