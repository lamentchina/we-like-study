class_1907 = Ext.extend(Ext.app.Module, {
			id : 'id_1907',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);
				var obj = new wls.subject();

				if (!win) {
					var winWidth = 650;
					var winHeight = 400;
					var obj = new wls.subject();

					win = desktop.createWindow({
								id : this.id,
								title : il8n.subject,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_excel_16_16',
								iconClsGhostBar : 'icon_excel_32_32',
								layout : 'fit',
								items : [obj.getTreeGrid('qd_w_u_l')]
								
							});
				}
				win.show();
			}
		});