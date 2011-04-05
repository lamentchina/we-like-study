wls.user.teacher = Ext.extend(wls.quiz, {
	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=user_teacher&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'markked', 'title', 'time_markked', 'id_teacher', 'id_user', 'name_subject']
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
								header : il8n.marking,
								dataIndex : 'markked',
								renderer : function(v){
									if(v==1){
										return il8n.markked;
									}else{
										return il8n.waitForMark;
									}
								}
							}, {
								header : il8n.title,
								dataIndex : 'time_markked',
								renderer : function(v){
									return v.substring(0,10);
								}
							}, {
								header : il8n.id+'('+il8n.teacher+')',
								dataIndex : 'id_teacher',
								hidden:true
							}, {
								header : il8n.id+'('+il8n.user+')',
								dataIndex : 'id_user',
								hidden:true
							}, {
								header : il8n.subject,
								dataIndex : 'name_subject'
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_u_p_l_tb",
					items : []
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : '100%',
					height : 430,
					loadMask : true,
					tbar : tb,

					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		store.load({
					params : {
						start : 0,
						limit : 15
					}
				});

		if (typeof(me) == "undefined") {
			//
		} else {
			var access = me.myUser.access.split(",");

			for (var i = 0; i < access.length; i++) {
				if (access[i] == '1909') {
					eval("var iconCls = 'bt_'+me.myUser.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = me.myUser.access2.p"+access[i]+"[2];");
					tb.add('-', {
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
								alert(il8n.clickCellInGrid);
								return;
							}
							if (Ext.getCmp(domid).getSelectionModel().selections.items[0].data.markked == 1) {
								alert(il8n.markked);
								return;
							}							
							var pid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;

							var uid = me.myUser.id;
							var desktop = QoDesk.App.getDesktop();

							var win = desktop.getWindow(pid + '_qdesk');
							
							var winWidth = desktop.getWinWidth();
							var winHeight = desktop.getWinHeight();
							
							var win = new Ext.Window({
								title : tooltip,
								id : pid + '_qdesk',
								layout : 'fit',
								width : winWidth,								
								height : winHeight,
								modal : true,
								html : "<iframe src ='"
											+ thisObj.config.AJAXPATH
											+ "?controller=user_teacher&action=viewQuiz&id="
											+ pid
											+ "&uid="
											+ uid
											+ '&temp='
											+ Math.random()
											+ "' style='width:100%; height:100%;' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} 
			}
		}

		return grid;
	}
	,
	type : 'teacherMark',
	id : null,
	id_quiz_log :null,
	paperData : null,

	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=user_teacher&action=getOne",
			data : {
				id : thisObj.id
			},
			type : "POST",
			success : function(msg) {
				var obj = jQuery.parseJSON(msg);
				thisObj.paperData = obj;
				thisObj.ids_questions = obj.ids_question;
				thisObj.state = 1;
				thisObj.id_quiz_log = obj.id_quiz_log;
				$.unblockUI();

				obj = null;
				eval(nextFunction);
	
			}
		});
	},

	submit : function(nextFunction) {
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});

		this.answersData = [];
		for (var i = 0; i < this.questions.length; i++) {
			var ans = {
				id : this.questions[i].id,
				answer : this.questions[i].getMyAnswer()
			};
			this.answersData.push(ans);
		}
		var thisObj = this;
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=user_teacher&action=finishMark",
			data : {
				id : thisObj.id_quiz_log
			},
			type : "POST",
			success : function(msg) {
				$.unblockUI();
			
			}
		});
	}
	,
	showResult : function() {

	}
	,ajaxQuestions : function(nextFunction) {
		var thisObj = this;
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>'
				});
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=user_teacher&action=getQuestionsByIds",
			data : {
				ids_questions : thisObj.ids_questions
				,id_quiz_log:thisObj.paperData.id_quiz_log
			},
			type : "POST",
			success : function(msg) {
				var obj = jQuery.parseJSON(msg);
				thisObj.questionsData = obj;
				thisObj.state = 2;
				$.unblockUI();
				Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Navigation');
				eval(nextFunction);	
				
				for(var i=0;i<thisObj.questions.length;i++){
					thisObj.questions[i].answerData = {
						answer:obj[i].answer
						,myAnswer:obj[i].myAnswer
					};

					thisObj.questions[i].setMyAnswer();
					thisObj.questions[i].markingmethod = obj[i].markingmethod;

					thisObj.questions[i].marking();
				}
				obj = null;
			}
		});
	}
});
