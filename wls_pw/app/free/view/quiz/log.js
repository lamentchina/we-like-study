wls.quiz.log = Ext.extend(wls.quiz, {

	type : 'log',
	id : null,
	logData : null,
	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});
		$.ajax({
					url : thisObj.config.AJAXPATH
							+ "?controller=quiz_log&action=getOne",
					data : {
						id : thisObj.id
					},
					type : "POST",
					success : function(msg) {						
						var obj = jQuery.parseJSON(msg);
						thisObj.logData = obj;
						thisObj.ids_questions = obj.ids_question;
						thisObj.state = 1;
						thisObj.addQuizBrief();
						thisObj.addNavigation();
						$.unblockUI();

						eval(nextFunction);
					}
				});
	},
	addQuizBrief : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td width='50%'>"
				+ il8n.date_created + "</td>" + "<td width='50%'>"
				+ this.logData.date_created + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.score_total + "</td>" + "<td>"
				+ this.logData.cent + "</td>" + "</tr>" + "<tr>" + "<td>"
				+ il8n.score + "</td>" + "<td>" + this.logData.mycent + "</td>"
				+ "</tr>" + "<tr>" + "<td>" + il8n.count_right + "</td>"
				+ "<td>" + this.logData.count_right + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.count_wrong + "</td>" + "<td>"
				+ this.logData.count_wrong + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.Quiz_Proportion + "</td>" + "<td>"
				+ this.logData.proportion + "</td>" + "</tr>" + "<tr>" + "<td>"
				+ il8n.Quiz_Application + "</td>" + "<td>"
				+ this.logData.application + "</td>" + "</tr>" + "</table>";
		$("#paperBrief").append(str);
	},
	submit : function(nextFunction) {
		if (this.state == 4 || this.state == 42)return;			

		var thisObj = this;
		$.ajax({
					url : thisObj.config.AJAXPATH
							+ "?controller=quiz_log&action=getAnswers",
					data : {
						id : thisObj.id
					},
					type : "POST",
					success : function(msg) {
						thisObj.state = 4;
						if(msg=='wrong'){
							alert(il8n.log_notfound);
						}else{
							var obj = thisObj.answersData = jQuery.parseJSON(msg);
							for (var i = 0; i < obj.length; i++) {
								thisObj.questions[i].answerData = obj[i];
								thisObj.questions[i].setMyAnswer();
							}
							thisObj.addDescriptions();
							eval(nextFunction);
						}
					}
				});
	},
	getListStuff : function() {
		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								width:40,
								header : il8n.id,
								dataIndex : 'id'
							}, {
								header : il8n.count_questions,
								dataIndex : 'count_questions'
							}, {
								header : il8n.title,
								dataIndex : 'title'
							}, {
								header : il8n.count_right,
								dataIndex : 'count_right'
							}, {
								header : il8n.count_wrong,
								dataIndex : 'count_wrong'
							}, {
								header : il8n.Quiz_Proportion,
								dataIndex : 'proportion',
								hidden : true
							}, {
								header : il8n.user,
								dataIndex : 'id_user',
								hidden : true
							}, {
								header : il8n.score,
								dataIndex : 'mycent'
							}, {
								header : il8n.score_total,
								dataIndex : 'cent'
							}, {
								header : il8n.Quiz_Application,
								dataIndex : 'name_application',
								hidden : true
							}

					]
				});

		return {
			cm : cm,
			fields : ['id', 'date_created',  'id_user',
					'cent', 'mycent', 'count_right', 'count_wrong',
					'count_giveup', 'count_total', 'name_subject',
					'count_questions', 'name_application','proportion','title']
		};
	},
	getList : function(domid) {
		var listStuff = this.getListStuff();
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_log&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : listStuff.fields
				});

		var tb = new Ext.Toolbar({
					id : "w_q_lg_l_tb"
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : listStuff.cm,
					id : domid,
					width : 600,
					height : 300,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		var access = user_.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '165101') {
				tb.add({
					iconCls: 'bt_importFile',
					tooltip : il8n.importFile,
					handler : function() {
						var win = new Ext.Window({
							id : 'w_q_p_l_i',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=quiz_log&action=importOne' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			} else if (access[i] == '165102') {
				tb.add({
					iconCls: 'bt_exportFile',
					tooltip : il8n.exportFile,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var win = new Ext.Window({
							id : 'w_q_p_l_e',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=quiz_log&action=exportOne' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			} else if (access[i] == '165103') {
				tb.add({
					iconCls: 'bt_deleteItems',
					tooltip : il8n.deleteItems,
					handler : function() {
					if (Ext.getCmp(domid).getSelectionModel().selection == null) {
						alert(il8n.clickCellInGrid);
						return;
					}
						if(Ext.getCmp(domid).getSelectionModel().selections.items.length == 0)return;
						Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH
									+ "?controller=quiz_log&action=delete",
							success : function(response) {
								store.load();
							},
							failure : function(response) {
								Ext.Msg.alert('failure', response.responseText);
							},
							params : {
								id : Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id
							}
						});
					}
				});
			} else if (access[i] == '165107') {
				tb.add({
					iconCls: 'bt_Quiz_Paper',
					tooltip : il8n.log_review,
					handler : function() {
					if (Ext.getCmp(domid).getSelectionModel().selection == null) {
						alert(il8n.clickCellInGrid);
						return;
					}
						var lid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;
						var uid = user_.myUser.id;
						var desktop = QoDesk.App.getDesktop();

						var win = desktop.getWindow(lid + '_qdesk');
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();

						if (!win) {
							win = desktop.createWindow({
								id : lid + '_qdesk',
								title : il8n.log_review,
								width : winWidth,
								height : winHeight,
								layout : 'fit',
								plain : false,
								html : '<iframe src="'
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_log&action=viewQuiz&id="
										+ lid
										+ "&uid="
										+ uid
										+ '&temp='
										+ Math.random()
										+ '" style="width:100%; height:100%;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
							});
						}
						win.show();
					}
				});
			}
		}

		store.load({
			params : {
				start : 0,
				limit : 8
			}
		});
		return grid;
	},
	getMyList : function(domid) {
		var listStuff = this.getListStuff();
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_log&action=getMyList',
					root : 'data',
					idProperty : 'id',
					fields : listStuff.fields
				});

		var tb = new Ext.Toolbar({
					id : "w_q_lg_ml_tb"
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : listStuff.cm,
					id : domid,
					width : 600,
					height : 300,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		var access = user_.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '125301') {
				tb.add({
					iconCls: 'bt_Quiz_Paper',
					tooltip : il8n.log_review,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selection == null) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var lid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;
						var uid = user_.myUser.id;
						var desktop = QoDesk.App.getDesktop();

						var win = desktop.getWindow(lid + '_qdesk');
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();

						if (!win) {
							win = desktop.createWindow({
								id : lid + '_qdesk',
								title : il8n.log_review,
								width : winWidth,
								height : winHeight,
								layout : 'fit',
								plain : false,
								html : '<iframe src="'
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_log&action=viewQuiz&id="
										+ lid
										+ "&uid="
										+ uid
										+ '&temp='
										+ Math.random()
										+ '" style="width:100%; height:100%;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
							});
						}
						win.show();

						// window.open(thisObj.config.AJAXPATH+"?controller=quiz_log&action=viewOne&id="+Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id);
					}
				});
			}
		}

		store.load({
					params : {
						start : 0,
						limit : 15
					}
				});
		return grid;
	}

});
