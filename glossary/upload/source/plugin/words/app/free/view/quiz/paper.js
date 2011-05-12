wls.quiz.paper = Ext.extend(wls.quiz, {

	type : 'paper',
	id : null,
	paperData : null,

	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({
					message : '<h1>' + il8n.loading + '</h1>......'
				});
		$.ajax({
					url : thisObj.config.AJAXPATH + "?controller=quiz_paper&action=getOne",
					data : {
						id : thisObj.id
					},
					type : "POST",
					success : function(msg) {
						var obj = jQuery.parseJSON(msg);
						thisObj.paperData = obj;
						thisObj.ids_questions = obj.ids_questions;
						thisObj.state = 1;
						thisObj.addQuizBrief();

						$.unblockUI();

						obj = null;
						eval(nextFunction);
					}
				});
	},
	addQuizBrief : function() {
		var str = 
			"<table width='90%' style='font-size:12px;'>" 
				+ "<tr>" 
					+ "<td width='25%'>" + il8n.name + "</td>"
					+ "<td width='75%'>" + this.paperData.title + "</td>" 
				+ "</tr>" 
				+ "<tr>" 
					+ "<td>" + il8n.score_top + "</td>" 
					+ "<td>" + this.paperData.score_top + "</td>"
				+ "</tr>" 
				+ "<tr>" 
					+ "<td>" + il8n.score_avg + "</td>" 
					+ "<td>" + this.paperData.score_avg + "</td>" 
				+ "</tr>" 
				+ "<tr>"
					+ "<td>" + il8n.money + "</td>" 
					+ "<td>" + this.paperData.money+ "</td>" 
				+ "</tr>" 
			"</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
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
			url : thisObj.config.AJAXPATH + "?controller=quiz_paper&action=getAnswers",
			data : {
				answersData : thisObj.answersData,
				id : thisObj.id
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
	},
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
	},
	getGrid : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
					autoDestroy : true,
					url : thisObj.config.AJAXPATH
							+ '?controller=quiz_paper&action=getList',
					root : 'data',
					remoteSort:true, 
					idProperty : 'id',
					fields : ['id', 'index', 'name_subject', 'title', 'money',
							'ids_questions', 'count_used', 'date_created2','id_level_subject']
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
								header : il8n.normal.id,
								dataIndex : 'id',
								hidden : true
							}, {
								header : il8n.normal.id + '(' + il8n.subject.subject + ')',
								dataIndex : 'id_level_subject',
								hidden : true
							}, {
								header : il8n.subject.subject,
								dataIndex : 'name_subject'
							}, {
								header : il8n.normal.title,
								dataIndex : 'title',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.user.money,
								dataIndex : 'money',
								editor : new Ext.form.TextField({
											allowBlank : false
										})
							}, {
								header : il8n.quiz.count_questions,
								dataIndex : 'ids_questions',
								renderer : function(value) {
									var json = '[' + value + ']';
									var arr = jQuery.parseJSON(json);
									return arr.length;
								}
							}, {
								header : il8n.quiz.count_used,
								dataIndex : 'count_used'
							}, {
								header : il8n.normal.date_created,
								dataIndex : 'date_created2'
							}]
				});

		var search = new Ext.form.TextField({
					id : domid + '_search',
					width : 170,
					enableKeyEvents : true
				});
		search.on('keyup', function(a, b, c) {
					if (b.button == 12) {
						store.load({
									params : {
										start : 0,
										limit : 15,
										search : Ext.getCmp(domid + '_search').getValue()
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

		var grid = new Ext.grid.EditorGridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : "100%",
					height : 400,
					clicksToEdit : 2,
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
				if (access[i] == '1101') {
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
								listeners : {
									'show':function(x){
										var c = document.getElementById('paper_import');   
										c.src =  thisObj.config.AJAXPATH + "?controller=quiz_paper&action=importOne";
									}
								},
								html : "<iframe id='paper_import' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				} else if (access[i] == '1102') {
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
							var pid = Ext.getCmp(domid).getSelectionModel().selection.record.id;
							var win = new Ext.Window({
								id : 'w_q_p_l_e',
								layout : 'fit',
								title : il8n.exportFile,
								modal : true,
								width : 500,
								height : 300,
								listeners : {
									'show':function(x){
										var c = document.getElementById('paper_export');   
										c.src = thisObj.config.AJAXPATH + "?controller=quiz_paper&action=exportOne&id="
										+ pid;
									}
								},
								html : "<iframe id='paper_export' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				}else if (access[i] == '1108') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							var win = new Ext.Window({
								id : 'w_q_p_l_e',
								layout : 'fit',
								title : il8n.exportAll,
								modal : true,
								width : 500,
								height : 200,
								listeners : {
									'show':function(x){
										var c = document.getElementById('paper_exportAll');   
										c.src = thisObj.config.AJAXPATH + "?controller=quiz_paper&action=exportAll";
									}
								},
								html : "<iframe id='paper_exportAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				}else if (access[i] == '1109') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add({
						iconCls: iconCls,
						tooltip : tooltip,
						handler : function() {
							var win = new Ext.Window({
								id : 'w_q_p_l_e',
								layout : 'fit',
								title : il8n.importAll,
								modal : true,
								width : 500,
								height : 200,
								listeners : {
									'show':function(x){
										var c = document.getElementById('paper_importAll');   
										c.src = thisObj.config.AJAXPATH + "?controller=quiz_paper&action=importAll";
									}
								},
								html : "<iframe id='paper_importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
							});
							win.show(this);
						}
					});
				}else if (access[i] == '1103') {
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
				} else if (access[i] == '1107') {
					eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
					eval("var tooltip = obj.access2.p"+access[i]+"[2];");
					tb.add( {
						//iconCls: iconCls,
						//tooltip : tooltip,
						text:tooltip,
						handler : function() {
							if (Ext.getCmp(domid).getSelectionModel().selection == null) {
								alert(il8n.clickCellInGrid);
								return;
							}
							var pid = Ext.getCmp(domid).getSelectionModel().selection.record.id;
							
							var uid = obj.id;
							if( typeof(parent.QoDesk)=='undefined' ){
								var win = new Ext.Window({
									 width: 400
									,height: 500
									,listeners : {
										'show':function(x){
											var c = document.getElementById('paper_vq_' + pid);   
											c.src = thisObj.config.AJAXPATH + "?controller=quiz_paper&action=viewQuiz&id="
													+ pid
													+ "&uid="
													+ uid
													+ '&temp='
													+ Math.random();
										}
									}									
									,html : '<iframe id="paper_vq_'+pid+'" style="width:100%; height:'+(winHeight-30)+'px;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
								});
								win.show();
							}else{
								var desktop = parent.QoDesk.App.getDesktop();
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
										listeners : {
											'show':function(x){
												var c = parent.document.getElementById('paper_vq_' + pid);   
												c.src = thisObj.config.AJAXPATH + "?controller=quiz_paper&action=viewQuiz&id="
														+ pid
														+ "&uid="
														+ uid
														+ '&temp='
														+ Math.random();
											}
										},
										html : '<iframe id="paper_vq_'+pid+'" style="width:100%; height:'+(winHeight-30)+'px;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
									});
								}
								win.show();
							}
							
						}
					});
				} else if (access[i] == '1104') {
					grid.on("afteredit", afteredit, grid);
				} else if (access[i] == '1105') {
					// TODO
				}
			}
		
				tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});
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