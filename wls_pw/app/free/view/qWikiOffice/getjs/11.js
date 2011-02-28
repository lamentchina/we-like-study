class_11 = Ext.extend(Ext.app.Module, {
			id : 'id_11',
			type : 'demo/tab',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = desktop.getWinWidth() / 1.1;
					var winHeight = desktop.getWinHeight() / 1.1;
					var quiz_paper = new wls.quiz.paper();

					win = desktop.createWindow({
								id : this.id,
								title : il8n.paper,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_paper_16_16',
								shim : false,
								constrainHeader : true,
								layout : 'fit',
								items : [quiz_paper.getList('qd_w_q_p_l')]
							});
				}
				win.show();
			}
		});