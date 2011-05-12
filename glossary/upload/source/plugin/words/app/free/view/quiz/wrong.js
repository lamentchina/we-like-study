wls.quiz.wrong = Ext.extend(wls.quiz, {

	type : 'wrong',
	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({message : '<h1>' + il8n.normal.loading + '</h1>'});
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
				+ il8n.normal.count_questions + "</td>" + "<td>" + this.count.total
				+ "</td>" + "</tr>" + "</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
	},
	submit : function(nextFunction) {
		$.blockUI({message : '<h1>' + il8n.normal.loading + '</h1>'});
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
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.normal.count_right
				+ "</td>" + "<td>" + this.count.right + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.normal.count_wrong + "</td>" + "<td>"
				+ this.count.wrong + "</td>" + "</tr>" + "<tr>" + "<td>"
				+ il8n.normal.count_giveup + "</td>" + "<td>" + this.count.giveup
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.normal.count_questions
				+ "</td>" + "<td>" + this.count.total + "</td>" + "</tr>"
				+ "</table>";
		var ac = Ext.getCmp('ext_Operations');
		ac.layout.activeItem.collapse(false);
		ac.add({
					id : 'ext_wrongResult',
					title : il8n.quiz.Quiz_Wrongs_Result,
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
	getGrid : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_wrong&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'title_question', 
							'date_created', 'timedif', 'count', 'id_user']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.normal.id,
								dataIndex : 'id'
							}, {
								header : il8n.normal.title,
								dataIndex : 'title_question'
							}, {
								header : il8n.normal.date_created,
								dataIndex : 'date_created',
								hidden : true
							}, {
								header : il8n.normal.date_created,
								dataIndex : 'timedif'
							}, {
								header : il8n.quiz.count_wrong,
								dataIndex : 'count'
							}, {
								header : il8n.user.username,
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
					width : '100%',
					height : 430,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=user&action=getCurrentUserSession",
			success : function(response) {
				var obj = Ext.decode(response.responseText);
				//console.debug(obj);
				var access = obj.access;
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '165303') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add( {
						iconCls: iconCls,
						tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
							alert(il8n.normal.clickCellInGrid);
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
								// TODO
								// Ext.Msg.alert('failure',response.responseText);
							},
							params : {
								ids : ids
							}
						});
					}
				});
			}}			
			tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});
		
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
								header : il8n.normal.id,
								dataIndex : 'id'
							}, {
								header : il8n.normal.subject,
								dataIndex : 'name_subject'
							}, {
								header : il8n.normal.subject+'(id)',
								dataIndex : 'id_level_subject',
								hidden : true
							}, {
								header : il8n.normal.paper,
								dataIndex : 'title_quiz'
							}, {
								header : il8n.normal.question,
								dataIndex : 'title_question'
							}, {
								header : il8n.normal.date_created+(2),
								dataIndex : 'date_created',
								hidden : true
							}, {
								header : il8n.normal.date_created,
								dataIndex : 'timedif'
							}, {
								header : il8n.normal.count_wrong,
								dataIndex : 'count'
							}, {
								header : il8n.normal.Qes_Type,
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
					width : '100%',
					height : 400,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=user&action=getCurrentUserSession",
			success : function(response) {
				var obj = Ext.decode(response.responseText);
				//console.debug(obj);
				var access = obj.access;
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '125003') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add( {
						iconCls: iconCls,
						tooltip : tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
							alert(il8n.normal.clickCellInGrid);
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
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add( {
						iconCls: iconCls,
						tooltip : tooltip,
					handler : function() {
						var uid = obj.id;
						var desktop = parent.QoDesk.App.getDesktop();

						var win = desktop.getWindow(uid + '_wrongs');
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();

						if (!win) {
							win = desktop.createWindow({
								id : uid + '_wrongs',
								title : il8n.quiz.Quiz_Wrongs,
								width : winWidth,
								height : winHeight,
								layout : 'fit',
								plain : false,
								listeners : {
									'show':function(x){
										var c = parent.document.getElementById('wrong_vq');   
										c.src = thisObj.config.AJAXPATH + "?controller=quiz_wrong&action=viewQuiz"
												+ "&uid="
												+ uid
												+ '&temp='
												+ Math.random();
									}
								},
								html : '<iframe id="wrong_vq" style="width:100%; height:'+(winHeight-30)+'px;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
							});
						}
						win.show();
					}
				});
			}}			
			tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});
		store.load({
					params : {
						start : 0,
						limit : 15
					}
				});
		return grid;
	}

});
