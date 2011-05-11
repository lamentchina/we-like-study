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
				+ "<td>" + this.examData.title + "</td>" + "</tr>" 
				 + "<tr>" + "<td>" + il8n.time_start + "</td>"
				+ "<td>" + this.examData.time_start.substring(0,10) + "</td>" + "</tr>"
				 + "<tr>" + "<td>" + il8n.time_stop + "</td>"
				+ "<td>" + this.examData.time_stop.substring(0,10) + "</td>" + "</tr>"
				 + "<tr>" + "<td>" + il8n.examPassLine + "</td>"
				+ "<td>" + this.examData.passline + "</td>" + "</tr>"				
				+ "<tr>" + "<td>" + il8n.time_spent
				+ "</td>" + "<td><span id='clock'>00</span></td>" + "</tr>"
				+ "</table>";
		$("#paperBrief").append(str);
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
					url : thisObj.config.AJAXPATH + "?controller=quiz_exam&action=getAnswers",
					data : {
						answersData : thisObj.answersData,
						id : thisObj.id,
						time_start : thisObj.time.start,
						time_stop : thisObj.time.stop,
						time_used : thisObj.time.used
					},
					type : "POST",
					success : function(msg) {
						$.unblockUI();
						var obj = thisObj.answersData = jQuery.parseJSON(msg);
						for (var i = 0; i < obj.length; i++) {
							thisObj.questions[i].answerData = obj[i];
						}
						
						eval(nextFunction);
						thisObj.showResult(obj[0].msg);
						
					}
				});
	}
	,
	showResult : function(msg) {
		var passed = il8n.exam_passed;
		if(this.mycent<this.examData.passline){
			passed = il8n.exam_failed;
		}
		passed = "<span style='color:red'>"+passed+"</span>"

		var str = "<table width='90%'>" 
				+ "<tr>" + "<td colspan='2'>" + msg + "</td>" + "</tr>" 
				+ "<tr>" + "<td colspan='2'>&nbsp;</td>" + "</tr>" 
				+ "<tr>" + "<td>" + il8n.score + "</td>" + "<td>" + this.mycent + "</td>" + "</tr>" 
				+ "<tr>" + "<td>" + il8n.Quiz_Paper_Result + "</td>" + "<td>" + passed + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.examPassLine + "</td>" + "<td>" + this.examData.passline + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.count_right + "</td>" + "<td>" + this.count.right + "</td>" + "</tr>"
				+ "<tr>" + "<td>" + il8n.count_wrong + "</td>" + "<td>" + this.count.wrong + "</td>" + "</tr>" 
				+ "<tr>" + "<td>" + il8n.count_giveup + "</td>" + "<td>" + this.count.giveup + "</td>" + "</tr>" 
				+ "<tr>" + "<td>" + il8n.count_questions + "</td>" + "<td>" + this.count.total + "</td>" + "</tr>"
				+ "</table>";
		var ac = Ext.getCmp('ext_Operations');

		ac.layout.activeItem.collapse(false);

		ac.add({
					id : 'ext_examResult',
					title : il8n.Quiz_Paper_Result,
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
	getGrid : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_exam&action=getList',
					root : 'data',
					idProperty : 'id',
					fields : ['id', 'index', 'title','time_start','time_stop','passline','count_groups','count_users','cent','mycent','count_used','name_subject','id_quiz']
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
								header : il8n.normal.id,
								dataIndex : 'id',
								hidden : true
							}, {
								header : il8n.normal.title,
								dataIndex : 'title'
							}, {
								header : il8n.subject.subject,
								dataIndex : 'name_subject'
							}, {
								header : il8n.normal.time_start,
								dataIndex : 'time_start',
								renderer : function(v){
									return v.substr(0,10);
								},
								hidden:true
							}, {
								header : il8n.normal.time_stop,
								dataIndex : 'time_stop',
								renderer : function(v){
									return v.substr(0,10);
								},
								hidden:true
							}, {
								header : il8n.quiz.examPassLine,
								dataIndex : 'passline',
								hidden : true
							}, {
								header : il8n.quiz.exam_count_groups,
								dataIndex : 'count_groups'
							}, {
								header : il8n.quiz.exam_count_users,
								dataIndex : 'count_users'
							}, {
								header : il8n.quiz.exam_count_done,
								hidden : true,
								dataIndex : 'count_used'
							}, {
								header : il8n.quiz.score_total,
								dataIndex : 'cent'
							}, {
								header : il8n.quiz.myScore,
								dataIndex : 'mycent'
							}, {
								header : il8n.normal.status
								,dataIndex : 'mycent'
						   		,renderer : function(v, meta, record, row_idx, col_idx, store){
						   			if(record.data.mycent!=null){
						   				if(record.data.mycent>=record.data.examPassLine){
						   					return il8n.quiz.exam_passed;
						   				}else{
						   					return '<span style="color:red">'+il8n.quiz.exam_failed+'</span>';
						   				}
						   			}
						   			var str = record.data.time_start;
						   			var arr = str.split(" ");
						   			arr[0] = arr[0].replace(/-/g,"/");
						   			//console.debug(arr[0]+" "+arr[1]);
						   			var d1 = new Date(arr[0]+" "+arr[1]);
						   			if(d1>new Date()){
						   				return il8n.quiz.exam_notready;
						   			}
						   			var str = record.data.time_stop;
						   			var arr = str.split(" ");
						   			arr[0] = arr[0].replace(/-/g,"/");
						   			//console.debug(arr[0]+" "+arr[1]);
						   			var d1 = new Date(arr[0]+" "+arr[1]);
						   			if(d1<new Date()){
						   				return '<span style="color:red">'+il8n.quiz.exam_yourhavelost+'</span>';
						   			}
									return il8n.quiz.exam_open;
				            	}
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
								iconCls: 'bt_search_16_16',
								tooltip : il8n.normal.search,	
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
					width : '100%',
					height : 500,
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
				if (access[i] == '2001') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
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
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
								alert(il8n.normal.ClickCellInGrid);
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
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
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
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
								alert(il8n.clickCellInGrid);
								return;
							}

							if (Ext.getCmp(domid).getSelectionModel().selections.items[0].data.mycent!=null) {
								alert(il8n.exam_already_done);
								return;
							}
							var str = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.time_start;
				   			var arr = str.split(" ");
				   			arr[0] = arr[0].replace(/-/g,"/");
				   			//console.debug(arr[0]+" "+arr[1]);
				   			var d1 = new Date(arr[0]+" "+arr[1]);
				   			if(d1>new Date()){
				   				alert( '未开始');
				   				return;
				   			}
				   			
				   			var str = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.time_stop;
				   			var arr = str.split(" ");
				   			arr[0] = arr[0].replace(/-/g,"/");
				   			//console.debug(arr[0]+" "+arr[1]);
				   			var d1 = new Date(arr[0]+" "+arr[1]);
				   			if(d1<new Date()){
				   				alert('旷考');
				   				return;
				   			}

							if (Ext.getCmp(domid).getSelectionModel().selections.items[0].data.mycent!=null) {
								alert(il8n.exam_already_done);
								return;
							}							

							var pid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;

							var uid = obj.id;
							var desktop = parent.QoDesk.App.getDesktop();

							var win = desktop.getWindow(pid + '_qdesk');
							var winWidth = desktop.getWinWidth();
							var winHeight = desktop.getWinHeight();

							if (!win) {
								win = desktop.createWindow({
									id : pid + '_qdesk',
									title : Ext.getCmp(domid).getSelectionModel().selections.items[0].data.title,
									width : winWidth,
									height : winHeight,
									layout : 'fit',
									plain : false,
									listeners : {
										'show':function(x){
											var c = parent.document.getElementById('exam_vq_' + pid);   
											c.src = thisObj.config.AJAXPATH + "?controller=quiz_exam&action=viewQuiz&id="
													+ pid
													+ "&uid="
													+ uid
													+ '&temp='
													+ Math.random();
										}
									},
									html : '<iframe id="exam_vq_'+pid+'" style="width:100%; height:'+(winHeight-30)+'px;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
								});
							}
							win.show();

						}
					});
				} else if (access[i] == '2009') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
								alert(il8n.clickCellInGrid);
								return;
							}
							var id_quiz = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id_quiz;
							var store2 = new Ext.data.JsonStore({
								autoDestroy : true,
								url : thisObj.config.AJAXPATH
										+ '?controller=quiz_log&action=getRankings&id_quiz='+id_quiz,
								root : 'data',
								idProperty : 'index',
								fields : [ 'index', 'username','mycent','column0']
							});

							var cm2 = new Ext.grid.ColumnModel({
										defaults : {
											sortable : false
										},
										columns : [{
												header : il8n.exam_rank,
												width : 40,
												dataIndex : 'index'
											},{
												header : il8n.score,
												width : 40,
												dataIndex : 'mycent'
											},{
												header : il8n.username,
												width : 40,
												dataIndex : 'username'
											},{
												header : '&nbsp',
												width : 40,
												dataIndex : 'column0'
											}]
										});
							var grid2 = new Ext.grid.GridPanel({
								store : store2,
								cm : cm2,
								id : 'haha',
								width : 100,
								height : 100
							});
							store2.load();
							
							var winx = new Ext.Window({
								id:'xxyyzzdd',
								title:'考试成绩',
								layout : 'fit',
								plain: true,
								width : 500,
								height : 300,
								items:[grid2]								
							});
							winx.show();							
						}
					});
				} else if (access[i] == '1105') {

				}
			}				
			tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});

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
