class_1651 = Ext.extend(Ext.app.Module, {
			id : 'id_1651',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = desktop.getWinWidth() / 1.1;
					var winHeight = desktop.getWinHeight() / 1.1;
					var obj = new wls.quiz.log();

					win = desktop.createWindow({
								id : this.id,
								title : il8n.log_allQuiz,
								width : winWidth,
								height : winHeight,

								layout : 'fit',
								items : [obj.getList('qd_w_q_l_l')]
								,
							});
				}
				win.show();
			}
		});