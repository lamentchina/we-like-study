class_1907 = Ext.extend(Ext.app.Module, {
			id : 'id_1907',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var obj = new wls.subject();

				if (!win) {
					var winWidth = 550;
					var winHeight = 300;
					var obj = new wls.subject();

					win = desktop.createWindow({
								id : this.id,
								title : il8n.subject,
								width : winWidth,
								height : winHeight,

								layout : 'fit',
								items : [obj.getList('qd_w_u_l')]
								,
							});
				}
				win.show();
			}
		});