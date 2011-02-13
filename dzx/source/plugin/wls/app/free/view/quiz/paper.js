wls.quiz.paper = Ext.extend(wls.quiz, {

	type : 'paper',
	id : null,
	paperData : null,
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
							+ "?controller=quiz_paper&action=getOne",
					data : {
						id : thisObj.id
					},
					type : "POST",
					success : function(msg) {
						var obj = jQuery.parseJSON(msg);
						thisObj.paperData = obj.data[0];
						thisObj.questionsIds = obj.data[0].questions;
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
						eval(nextFunction);
					}
				});
	},
	addQuizBrief : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.name + "</td>"
				+ "<td>" + this.paperData.title + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.time_limit + "</td>" + "<td>"
				+ this.get_elapsed_time_string(this.paperData.time_limit)
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.score_top
				+ "</td>" + "<td>" + this.paperData.score_top + "</td>"
				+ "</tr>" + "<tr>" + "<td>" + il8n.score_avg + "</td>" + "<td>"
				+ this.paperData.score_avg + "</td>" + "</tr>" + "<tr>"
				+ "<td>" + il8n.money + "</td>" + "<td>" + this.paperData.money
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.time_spent
				+ "</td>" + "<td><span id='clock'>00</span></td>" + "</tr>"
				+ "</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
		wls_quiz_paper_clock = setInterval(function() {
					thisObj.time.used++;
					$('#clock').text(thisObj
							.get_elapsed_time_string(thisObj.time.used));
				}, 1000);
	},
	submit : function(nextFunction) {
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});

		window.clearInterval(wls_quiz_paper_clock);
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
							+ "?controller=quiz_paper&action=getAnswers",
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
					id : 'ext_PaperResult',
					title : il8n.Quiz_Paper_Result,
					html : '<div id="paperresult">aaa</div>'
				});
		ac.doLayout();

		$("#paperresult").empty();
		$("#paperresult").append(str);

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
							+ '?controller=quiz_paper&action=jsonList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'index', 'name_subject', 'title', 'money',
							'questions', 'count_used', 'date_created2']
				});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
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
								header : il8n.subject,
								dataIndex : 'name_subject'
							}, {
								header : il8n.title,
								dataIndex : 'title',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.count_questions,
								dataIndex : 'questions',
								renderer : function(value) {
									var json = '[' + value + ']';
									var arr = jQuery.parseJSON(json);
									return arr.length;
								}
							}, {
								header : il8n.count_used,
								dataIndex : 'count_used'
							}, {
								header : il8n.date_created,
								dataIndex : 'date_created2'
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

		var grid = new Ext.grid.EditorGridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : 600,
					height : 300,
					clicksToEdit : 2,
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
			var privilege = user_.myUser.privilege.split(",");
			for (var i = 0; i < privilege.length; i++) {
				if (privilege[i] == '1101') {
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
										+ "?controller=quiz_paper&action=viewUpload' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (privilege[i] == '1102') {
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
										+ "?controller=quiz_paper&action=viewExport&id="
										+ pid
										+ "' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (privilege[i] == '1103') {
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
										+ "?controller=quiz_paper&action=delete",
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
				} else if (privilege[i] == '1107') {
					tb.add('-', {
						text : il8n.Quiz_Paper,
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
											+ "?controller=quiz_paper&action=viewOne&id="
											+ pid
											+ "&uid="
											+ uid
											+ '&temp='
											+ Math.random()
											+ '" style="width:100%; height:100%;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
								});
							}
							win.show();

							// window.open(thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewOne&id="+Ext.getCmp(domid).getSelectionModel().selection.record.id+"&uid="+user_.myUser.id);
						}
					});
				} else if (privilege[i] == '1104') {
					grid.on("afteredit", afteredit, grid);
				} else if (privilege[i] == '1105') {
					// TODO
				}
			}
		}
		function afteredit(e) {
			Ext.Ajax.request({
						method : 'POST',
						url : thisObj.config.AJAXPATH
								+ "?controller=quiz_paper&action=saveUpdate",
						success : function(response) {
							// TODO
						},
						failure : function(response) {
							// TODO
							// Ext.Msg.alert('failure',response.responseText);
						},
						params : {
							field : e.field,
							value : e.value,
							id : e.record.data.id
						}
					});
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
var wls_quiz_paper_clock = null;
