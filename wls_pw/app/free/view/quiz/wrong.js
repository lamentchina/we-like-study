wls.quiz.wrong = Ext.extend(wls.quiz, {

	type : 'wrong',
	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({message : '<h1>' + il8n.loading + '</h1>'});
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=quiz_wrong&action=getOne",
			type : "POST",
			success : function(msg) {
				thisObj.ids_questions = msg;
				var temp = jQuery.parseJSON('[' + msg + ']');
				thisObj.count.total = temp.length;
				thisObj.state = 1;
				thisObj.addQuizBrief();
				thisObj.addNavigation();
				$.unblockUI();
				eval(nextFunction);
			}
		});
	},
	addQuizBrief : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td>"
				+ il8n.count_questions + "</td>" + "<td>" + this.count.total
				+ "</td>" + "</tr>" + "</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
	},
	submit : function(nextFunction) {
		$.blockUI({message : '<h1>' + il8n.loading + '</h1>'});
		this.answersData = [];
		for (var i = 0; i < this.questions.length; i++) {
			this.answersData.push({
				id : this.questions[i].id,
				answer : this.questions[i].getMyAnswer()
			});
		}
		var thisObj = this;
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=quiz_wrong&action=getAnswers",
			data : {
				answersData : thisObj.answersData,
				id_level_subject : thisObj.id_level_subject
			},
			type : "POST",
			success : function(msg) {
				$.unblockUI();
				var obj = thisObj.answersData = jQuery.parseJSON(msg);
				for (var i = 0; i < obj.length; i++) {
					thisObj.questions[i].answerData = obj[i];
				}
				eval(nextFunction);
				thisObj.showResult();
			}
		});
	}
	,
	showResult : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.count_right
				+ "</td>" + "<td>" + this.count.right + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.count_wrong + "</td>" + "<td>"
				+ this.count.wrong + "</td>" + "</tr>" + "<tr>" + "<td>"
				+ il8n.count_giveup + "</td>" + "<td>" + this.count.giveup
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.count_questions
				+ "</td>" + "<td>" + this.count.total + "</td>" + "</tr>"
				+ "</table>";
		var ac = Ext.getCmp('ext_Operations');
		ac.layout.activeItem.collapse(false);
		ac.add({
					id : 'ext_wrongResult',
					title : il8n.Quiz_Wrongs_Result,
					html : '<div id="wrongresult">aaa</div>'
				});
		ac.doLayout();

		$("#wrongresult").empty();
		$("#wrongresult").append(str);

		$.blockUI({
					message : str
				});
		$('.blockOverlay').attr('title', 'Click to unblock').click($.unblockUI);
	},
	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_wrong&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'title', 
							'date_created', 'timedif', 'count',
							'id_user']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.id,
								dataIndex : 'id'
							}, {
								header : il8n.title,
								dataIndex : 'title'
							}, {
								header : il8n.date_created,
								dataIndex : 'date_created',
								hidden : true
							}, {
								header : il8n.date_created,
								dataIndex : 'timedif'
							},  {
								header : il8n.count_wrong,
								dataIndex : 'count'
							}, {
								header : il8n.user,
								dataIndex : 'id_user'
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_q_w_l_tb" + domid
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : cm,
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
			if (access[i] == '115303') {
				tb.add({
					iconCls: 'bt_deleteItems',
					tooltip : il8n.deleteItems,
					handler : function() {
						Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH + "?controller=quiz_paper&action=delete",
							success : function(response) {
								store.load();
							},
							failure : function(response) {
								// TODO
								// Ext.Msg.alert('failure',response.responseText);
							},
							params : {
								id : Ext.getCmp(domid).getSelectionModel().selection.record.id
							}
						});
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
	},
	getMyList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_wrong&action=getMyList',
					root : 'data',
					idProperty : 'id',
					fields : ['id'
					          ,'id_quiz_paper'
					          ,'date_created' 
					          ,'timedif'
					          ,'count'
					          ,'title_question'							
					          ,'id_user'
					          ,'type'
					          ,'count_right'
					          ,'count_wrong'
					          ,'count_giveup'
					          ,'comment_ywrong_1'
					          ,'comment_ywrong_2'
					          ,'comment_ywrong_3'
					          ,'comment_ywrong_4'
					          ,'difficulty'
					          ,'markingmethod'
					          ,'title_quiz'
					          ,'author'
					          ,'name_subject'
					          ,'id_level_subject'
					          ,'count_used'
					]
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.id,
								dataIndex : 'id'
							}, {
								header : il8n.subject,
								dataIndex : 'name_subject'
							}, {
								header : il8n.subject+'(id)',
								dataIndex : 'id_level_subject',
								hidden : true
							}, {
								header : il8n.paper,
								dataIndex : 'title_quiz'
							}, {
								header : il8n.question,
								dataIndex : 'title_question'
							}, {
								header : il8n.date_created+(2),
								dataIndex : 'date_created',
								hidden : true
							}, {
								header : il8n.date_created,
								dataIndex : 'timedif'
							}, {
								header : il8n.count_wrong,
								dataIndex : 'count'
							}, {
								header : il8n.Qes_Type,
								dataIndex : 'type',
								hidden : true
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_q_w_ml_tb" + domid
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : cm,
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
			if (access[i] == '125003') {
				tb.add({
					iconCls: 'bt_deleteItems',
					tooltip : il8n.deleteItems,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var ids = '';
						var items = Ext.getCmp(domid).getSelectionModel().selections.items;
						for (var i = 0; i < items.length; i++) {
							ids += items[i].data.id + ',';
						}
						ids = ids.substring(0, ids.length - 1);
						Ext.Ajax.request({
							method : 'POST',
							url : thisObj.config.AJAXPATH
									+ "?controller=quiz_wrong&action=delete",
							success : function(response) {
								store.load();
							},
							failure : function(response) {
								Ext.Msg.alert('failure', response.responseText);
							},
							params : {
								ids : ids
							}
						});
					}
				});
			} else if (access[i] == '125007') {
				tb.add({
					iconCls: 'bt_Quiz_Paper',
					tooltip : il8n.Quiz_Wrongs,
					handler : function() {
						var uid = user_.myUser.id;
						var desktop = QoDesk.App.getDesktop();

						var win = desktop.getWindow(uid + '_wrongs');
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();

						if (!win) {
							win = desktop.createWindow({
								id : uid + '_wrongs',
								title : il8n.Quiz_Wrongs,
								width : winWidth,
								height : winHeight,
								layout : 'fit',
								plain : false,
								html : '<iframe src="'
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_wrong&action=viewQuiz"
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
						limit : 15
					}
				});
		return grid;
	}

});
