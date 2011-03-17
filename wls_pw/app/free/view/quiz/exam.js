wls.quiz.exam = Ext.extend(wls.quiz, {

	type : 'exam',
	id : null,
	examData : null,
	time : {
		start : null,
		stop : null,
		used : 0
	},
	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});
		$.ajax({
					url : thisObj.config.AJAXPATH
							+ "?controller=quiz_exam&action=getOne",
					data : {
						id : thisObj.id
					},
					type : "POST",
					success : function(msg) {
						var obj = jQuery.parseJSON(msg);
						thisObj.examData = obj;
						thisObj.ids_questions = obj.ids_questions;
						thisObj.state = 1;
						thisObj.addQuizBrief();
						thisObj.addNavigation();
						$.unblockUI();

						var objDate = new Date();
						var year = objDate.getFullYear();
						var month = objDate.getMonth() + 1;
						var day = objDate.getDate();
						var hour = objDate.getHours();
						var minute = objDate.getMinutes();
						var second = objDate.getSeconds();

						thisObj.time.start = year + "-" + month + "-" + day
								+ " " + hour + ":" + minute + ":" + second;
						// console.debug(thisObj);
						obj = null;
						eval(nextFunction);
					}
				});
	},
	addQuizBrief : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.name + "</td>"
				+ "<td>" + this.examData.title + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.time_limit + "</td>" + "<td>"
				+ this.get_elapsed_time_string(this.examData.time_limit)
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.score_top
				+ "</td>" + "<td>" + this.examData.score_top + "</td>"
				+ "</tr>" + "<tr>" + "<td>" + il8n.score_avg + "</td>" + "<td>"
				+ this.examData.score_avg + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.money + "</td>" + "<td>" + this.examData.money
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.time_spent
				+ "</td>" + "<td><span id='clock'>00</span></td>" + "</tr>"
				+ "</table>";
		$("#examBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
		wls_quiz_exam_clock = setInterval(function() {
					thisObj.time.used++;
					$('#clock').text(thisObj
							.get_elapsed_time_string(thisObj.time.used));
				}, 1000);
	},
	submit : function(nextFunction) {
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});

		window.clearInterval(wls_quiz_exam_clock);
		this.answersData = [];
		for (var i = 0; i < this.questions.length; i++) {
			// if(this.questions[i].type=='Qes_Big')continue;
			// if(this.questions[i].getMyAnswer()!='I_DONT_KNOW'){
			var ans = {
				id : this.questions[i].id,
				answer : this.questions[i].getMyAnswer()
			};
			this.answersData.push(ans);
			// }
		}
		var thisObj = this;

		var objDate = new Date();
		var year = objDate.getFullYear();
		var month = objDate.getMonth() + 1;
		var day = objDate.getDate();
		var hour = objDate.getHours();
		var minute = objDate.getMinutes();
		var second = objDate.getSeconds();

		thisObj.time.stop = year + "-" + month + "-" + day + " " + hour + ":"
				+ minute + ":" + second;
		$.ajax({
					url : thisObj.config.AJAXPATH
							+ "?controller=question&action=getByIds",
					data : {
						answersData : thisObj.answersData,
						id : thisObj.id,
						time : thisObj.time
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
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.score
				+ "</td>" + "<td>" + this.mycent + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.score_total + "</td>" + "<td>" + this.cent
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.count_right
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
					id : 'ext_examResult',
					title : il8n.Quiz_exam_Result,
					html : '<div id="examresult">aaa</div>'
				});
		ac.doLayout();

		$("#examresult").empty();
		$("#examresult").append(str);

		$.blockUI({
					message : str
				});
		$('.blockOverlay').attr('title', il8n.click2unblock).click($.unblockUI);
	}

	,
	get_elapsed_time_string : function(total_seconds) {
		function pretty_time_string(num) {
			return (num < 10 ? "0" : "") + num;
		}
		var hours = Math.floor(total_seconds / 3600);
		total_seconds = total_seconds % 3600;

		var minutes = Math.floor(total_seconds / 60);
		total_seconds = total_seconds % 60;

		var seconds = Math.floor(total_seconds);

		hours = pretty_time_string(hours);
		minutes = pretty_time_string(minutes);
		seconds = pretty_time_string(seconds);
		var currentTimeString = hours + ":" + minutes + ":" + seconds;
		return currentTimeString;
	},
	getList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_exam&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'index', 'title','time_start','time_stop','passline','count_groups','count_users']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : false
					},
					columns : [{
								header : '&nbsp;',
								width : 40,
								dataIndex : 'index'
							}, {
								header : il8n.id,
								dataIndex : 'id',
								hidden : true
							}, {
								header : il8n.title,
								dataIndex : 'title'
							}, {
								header : il8n.time_start,
								dataIndex : 'time_start'
							}, {
								header : il8n.time_stop,
								dataIndex : 'time_stop'
							}, {
								header : il8n.examPassLine,
								dataIndex : 'passline'
							}, {
								header : il8n.exam_count_groups,
								dataIndex : 'count_groups'
							}, {
								header : il8n.exam_count_users,
								dataIndex : 'count_users'
							}]
				});

		var search = new Ext.form.TextField({
					id : domid + '_search',
					width : 135,
					enableKeyEvents : true
				});
		search.on('keyup', function(a, b, c) {
					if (b.button == 12) {
						store.load({
									params : {
										start : 0,
										limit : 15,
										search : Ext.getCmp(domid + '_search')
												.getValue()
									}
								});
					}
				});
		var tb = new Ext.Toolbar({
					id : "w_s_l_tb",
					items : [search, {
								text : il8n.search,
								handler : function() {
									store.load({
												params : {
													start : 0,
													limit : 15
												}
											});
								}
							}, '-']
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

		if (typeof(user_) == "undefined") {
			//
		} else {
			var access = user_.myUser.access.split(",");
			for (var i = 0; i < access.length; i++) {
				if (access[i] == '2001') {
					tb.add({
						text : il8n.importFile,
						handler : function() {
							var win = new Ext.Window({
								id : 'w_q_p_l_i',
								layout : 'fit',
								width : 500,
								height : 300,
								title : il8n.importFile,
								modal : true,
								html : "<iframe src ='"
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_exam&action=importOne' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (access[i] == '2002') {
					tb.add({
						text : il8n.exportFile,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selection == null) {
								alert(il8n.clickCellInGrid);
								return;
							}
							var pid = Ext.getCmp(domid).getSelectionModel().selection.record.id;
							var win = new Ext.Window({
								id : 'w_q_p_l_e',
								layout : 'fit',
								title : il8n.exportFile,
								modal : true,
								width : 500,
								height : 300,
								html : "<iframe src ='"
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_exam&action=viewExport&id="
										+ pid
										+ "' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (access[i] == '2003') {
					tb.add({
						text : il8n.deleteItems,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selection == null) {
								alert(il8n.clickCellInGrid);
								return;
							}
							Ext.Ajax.request({
								method : 'POST',
								url : thisObj.config.AJAXPATH
										+ "?controller=quiz_exam&action=delete",
								success : function(response) {
									store.load();
								},
								failure : function(response) {
									Ext.Msg.alert('failure',
											response.responseText);
								},
								params : {
									id : Ext.getCmp(domid).getSelectionModel().selection.record.id
								}
							});
						}
					});
				} else if (access[i] == '2006') {
					tb.add('-', {
						iconCls : 'bt_Quiz_Paper',
						tooltip : il8n.Quiz_Paper,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selection == null) {
								alert(il8n.clickCellInGrid);
								return;
							}
							var pid = Ext.getCmp(domid).getSelectionModel().selection.record.id;

							var uid = user_.myUser.id;
							var desktop = QoDesk.App.getDesktop();

							var win = desktop.getWindow(pid + '_qdesk');
							var winWidth = desktop.getWinWidth();
							var winHeight = desktop.getWinHeight();

							if (!win) {
								win = desktop.createWindow({
									id : pid + '_qdesk',
									title : Ext.getCmp(domid)
											.getSelectionModel().selection.record.data.title,
									width : winWidth,
									height : winHeight,
									layout : 'fit',
									plain : false,
									html : '<iframe src="'
											+ thisObj.config.AJAXPATH
											+ "?controller=quiz_exam&action=viewOne&id="
											+ pid
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
				} else if (access[i] == '2004') {
					
				} else if (access[i] == '1105') {

				}
			}
		}

		store.on('beforeload', function() {
					Ext.apply(this.baseParams, {
								search : Ext.getCmp(domid + '_search')
										.getValue()
							});
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
var wls_quiz_exam_clock = null;
