class_1653 = Ext.extend(Ext.app.Module, {
			id : 'id_1653',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = desktop.getWinWidth() / 1.1;
					var winHeight = desktop.getWinHeight() / 1.1;
					var obj = new wls.quiz.wrong();

					win = desktop.createWindow({
								id : this.id,
								title : il8n.log_allWrongs,
								width : winWidth,
								height : winHeight,

								layout : 'fit',
								items : [obj.getList('qd_w_q_w_l')]
								,
							});
				}
				win.show();
			}
		});