class_1253 = Ext.extend(Ext.app.Module, {
			id : 'id_1253',

			init : function() {

			},

			createWindow : function() {
				var desktop = this.app.getDesktop();
				var win = desktop.getWindow(this.id);

				if (!win) {
					var winWidth = desktop.getWinWidth() / 1.1;
					var winHeight = 460;

					win = desktop.createWindow({
								id : this.id,
								title : il8n.quiz.log_allMyQuiz,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_word_16_16',
								iconClsGhostBar : 'icon_word_32_32',
								layout : 'fit',
								html : "<iframe src ='quiz/log/myGrid.html' width='100%' height='430' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
				}
				win.show();
			}
		});