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
					var winHeight = 430;

					win = desktop.createWindow({
								id : this.id,
								title : il8n.quiz.paper,
								width : winWidth,
								height : winHeight,
								iconCls : 'icon_paper_16_16',
								iconClsGhostBar : 'icon_paper_32_32',
								shim : false,
								constrainHeader : true,
								layout : 'fit',
								listeners : {
									'show':function(x){
										var c = document.getElementById('paperList');   
										c.src =  "quiz/paper/grid.html";
									}
								},
								html : "<iframe id='paperList' width='100%' height='430' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
					/*
					win = new Ext.Window({
						width : winWidth,
						height : winHeight,
						html : "<iframe src ='"
							+ me.config.AJAXPATH
							+ "?controller=quiz_paper&action=viewGetList' width='"+winWidth+"' height='430' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
					});
					*/
				}
				win.show();
			}
		});