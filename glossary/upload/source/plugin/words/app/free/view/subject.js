wls.subject = Ext.extend(wls, {
	id_level : null	,

	getAddItemForm : function() {
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id : 'w_s_ai_f',
			labelWidth : 75,
			frame : true,
			bodyStyle : 'padding:5px 5px 0',
			width : 350,
			defaults : {
				width : 100
			},
			defaultType : 'textfield',

			items : [{
						fieldLabel : il8n.normal.id_level,
						width : 150,
						regex:/^[0-9]/,
						name : 'id_level',
						allowBlank : false
					}, {
						fieldLabel : il8n.normal.name,
						width : 150,
						name : 'name',
						allowBlank : false
					}, {
						fieldLabel : il8n.normal.icon,
						width : 150,
						vtype : "alphanum",
						name : 'icon'
						//allowBlank : false
					}, {
						fieldLabel : il8n.normal.description,
						width : 150,
						name : 'description'
						//allowBlank : true
					}, {
						fieldLabel : il8n.user.isknowledge,
						width : 150,
						name : 'isknowledge',
						maxLength : 1,
						regex:/^[10]/
						//allowBlank : false
					}],

			buttons : [{
				text : il8n.save,
				handler : function() {
					var form = Ext.getCmp('w_s_ai_f').getForm();

					if (form.isValid()) {
						$.blockUI({
									message : '<h1>' + il8n.loading
											+ '......</h1>'
								});
						var obj = form.getValues();
						Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=subject&action=add&temp="
											+ Math.random(),
									success : function(response) {
										$.unblockUI();
										if(response.responseText==0){
											alert(il8n.fail);
										}else{										
											Ext.getCmp('subject_add_win').close();
										}
									},
									failure : function(response) {
										// TODO
										$.unblockUI();
									},
									params : obj
								});
					} else {
						Ext.Msg.alert(il8n.fail, il8n.RequesttedImputMissing);
					}
				}
			}]
		});
		return form;
	},
	
	getTreeGrid:function(domid){
		var thisObj = this;
		var record = Ext.data.Record.create([
		{name: 'name'},
		{name: 'icon'},
		{name: 'description'},	
		{name: 'isshortcut'},	
		{name: 'isknowledge'},	
		{name: 'questiontypes'},
		{name: 'id_level',type:'string'},
		{name: 'id',type:'int'},
		{name: '_parent', type: 'auto'},
		{name: '_is_leaf', type: 'bool'}
		]);
		var store = new Ext.ux.maximgb.tg.AdjacencyListStore({
		 	autoLoad : true,
		 	url: thisObj.config.AJAXPATH
				+ "?controller=subject&action=getTreeGrid&temp="
				+ Math.random(),
			reader: new Ext.data.JsonReader({
				id: 'id',
				root: 'data',
				totalProperty: 'total',
				successProperty: 'success'
			}, 
			record)
		});
		var tb = new Ext.Toolbar({
					id : "w_s_l_tb" + domid
				});
		var grid = new Ext.ux.maximgb.tg.EditorGridPanel({
		   store: store,
		   id : domid,
		   tbar : tb,
		   width:'100%',
		   height:400,
		   master_column_id : 'name',
		   columns: [
		   	{
		   		 id:'name'
		   		,header: il8n.name
		   		//,sortable: true
		   		,width: 75
		   		,dataIndex: 'name'
		   		,editor: new Ext.form.TextField()
		   		,renderer : function(v, meta, record, row_idx, col_idx, store){
		   			if(record.data.isknowledge==1){
		   				return '<span style="color:red">'+v+'</span>';
		   			}
               		return v;
            	}
			},{
			    header: il8n.icon, 
				width: 75, 
				//sortable: true, 
				dataIndex: 'icon',
				hidden: true,
				editor: new Ext.form.TextField()
			},{
			    header: il8n.id_level, 
				//width: 75, 
				//sortable: true, 
				dataIndex: 'id_level',
				editor: new Ext.form.TextField()
			},{
			    header: il8n.description, 
				width: 75, 
				//sortable: true, 
				dataIndex: 'description',
				editor: new Ext.form.TextField()
			},{
			    header: il8n.isshortcut, 
				width: 75, 
				//sortable: true, 
				dataIndex: 'isshortcut',
				editor: new Ext.form.TextField()
			},{
			    header: il8n.isknowledge, 
				width: 75, 
				hidden: true, 
				dataIndex: 'isknowledge',
				editor: new Ext.form.TextField()
			},{
			    header: il8n.Qes_Type, 
				width: 75, 
				hidden: true, 
				dataIndex: 'questiontypes',
				editor: new Ext.form.TextField()
			}],
			autoExpandColumn: 'name'
		});

		//Get the current user's access , add some operable buttons to the toole bar 
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=user&action=getCurrentUserSession",
			success : function(response) {
				var obj = Ext.decode(response.responseText);
				//console.debug(obj);
				var access = obj.access;
				for (var i = 0; i < access.length; i++) {
					if (access[i] == '190701'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_s_gp_l_i',
									layout : 'fit',
									width : 500,
									height : 300,
									listeners : {
										'show':function(x){
											var c = document.getElementById('subject_import');   
											c.src =  thisObj.config.AJAXPATH + "?controller=subject&action=importAll";
										}
									},
									html : "<iframe id='subject_import' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
								});
								win.show(this);
							}
						});
					} else if (access[i] == '190702') {
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
		
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_s_gp_l_e',
									layout : 'fit',
									width : 500,
									height : 300,
									listeners : {
										'show':function(x){
											var c = document.getElementById('subject_export');   
											c.src =  thisObj.config.AJAXPATH + "?controller=subject&action=exportAll";
										}
									},
									html : "<iframe id='subject_export' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
								});
								win.show(this);
							}
						});
					} else if (access[i] == '190703') {
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.clickCellInGrid);
									return;
								}
								Ext.MessageBox.confirm( Ext.MessageBox.buttonText.ok+'?', il8n.sureToDelete+"?<br/>"+il8n.cascadingDelete, function(button,text){  
		               				if(button=='yes'){
		               					Ext.Ajax.request({
											method : 'POST',
											url : thisObj.config.AJAXPATH
													+ "?controller=subject&action=delete",
											success : function(response) {
												Ext.getCmp(domid).getView().refresh();
												store.reload();
											},
											failure : function(response) {
												Ext.Msg.alert('failure',
														response.responseText);
											},
											params : {
												id : Ext.getCmp(domid)
														.getSelectionModel().selection.record.id
											}
										});
		               				}
		            			});
							}
						});
					} else if (access[i] == '190704') {
						grid.on("afteredit",function(e){
								Ext.Ajax.request({
											method : 'POST',
											url : thisObj.config.AJAXPATH
													+ "?controller=subject&action=saveUpdate",
											success : function(response) {
												// Ext.Msg.alert('success',response.responseText);
											},
											failure : function(response) {
												Ext.Msg.alert('failure', response.responseText);
											},
											params : {
												field : e.field,
												value : e.value,
												id : e.record.data.id
											}
										});
							
					 	});
					} else if (access[i] == '190705') {
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls: iconCls,
							tooltip : tooltip,
								handler : function() {
									var form = thisObj.getAddItemForm();
									var w = new Ext.Window({
												title : il8n.addNewSubject,
												id : 'subject_add_win',
												width : 350,
												height : 250,
												layout : 'fit',
												buttonAlign : 'center',
												items : [form],
												modal : true
											});
	
									w.show();
								}
							});
					}
				}
				tb.doLayout();
			},
			failure : function(response) {
				alert('Net connection failed.');
			}
		});
		
		return grid;
	}
	,
	getPaperList : function(domid) {
		var thisObj = this;
		var store = new Ext.data.JsonStore({
			autoDestroy : true,
			url : thisObj.config.AJAXPATH
					+ '?controller=subject&action=getPaperList&id_level_subject='
					+ thisObj.id_level,
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'title', 'ids_questions', 'count_used', 'money',
					'score_avg', 'score_top', 'score_top_user', 'time_limit']
		});

		var cm = new Ext.grid.ColumnModel({
					defaults : {
						sortable : true
					},
					columns : [{
								header : il8n.id,
								dataIndex : 'id',
								width : 50
							}, {
								header : il8n.title,
								dataIndex : 'title'
							}, {
								header : il8n.count_used,
								dataIndex : 'count_used',
								hidden : true
							}, {
								header : il8n.money,
								dataIndex : 'money'
							}, {
								header : il8n.score_avg,
								dataIndex : 'score_avg',
								hidden : true
							}, {
								header : il8n.score_top,
								dataIndex : 'score_top',
								hidden : true
							}, {
								header : il8n.count_questions,
								dataIndex : 'ids_questions',
								renderer : function(value) {
									var json = '[' + value + ']';
									var arr = jQuery.parseJSON(json);
									return arr.length;
								}
							}]
				});

		var tb = new Ext.Toolbar({
					id : "w_s_gp_l_tb" + domid
				});

		var grid = new Ext.grid.GridPanel({
					store : store,
					cm : cm,
					id : domid,
					width : "100%",
					height : 500,
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		var access = obj.access.split(",");
		for (var i = 0; i < access.length; i++) {			
			if (access[i] == '1107') {
				//eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = obj.access2.p"+access[i]+"[2];");
				tb.add( {
					//iconCls: iconCls,
					//tooltip : tooltip,
					text:tooltip,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var items = Ext.getCmp(domid).getSelectionModel().selections.items;
						var pid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;

						var uid = obj.id;
						var desktop = parent.QoDesk.App.getDesktop();

						var win = desktop.getWindow(pid + '_qdesk');
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();

						if (!win) {
							win = desktop.createWindow({
								id2 : pid,
								id : pid + '_qdesk',
								title : Ext.getCmp(domid).getSelectionModel().selections.items[0].data.title,
								width : winWidth,
								height : winHeight,
								layout : 'fit',
								plain : false,
								listeners : {
									'show':function(x){
										var c = parent.document.getElementById('x_'+x.id2);   
										c.src =  thisObj.config.AJAXPATH + "?controller=quiz_paper&action=viewQuiz&id="
												+ x.id2
												+ "&uid="
												+ uid
												+ '&temp='
												+ Math.random();
									}
								},
								html : '<iframe id="x_'+pid+'" style="width:100%; height:'+(winHeight-30)+'px;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
							});
						}
						win.show();
					}
				});
			}else if (access[i] == '1110') {
				eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
				eval("var tooltip = obj.access2.p"+access[i]+"[2];");
	
				Ext.Ajax.request({
					method : 'GET',
					url : thisObj.config.AJAXPATH
							+ "?controller=subject&action=getQuestionTypes&id_level="+thisObj.id_level,
					success : function(response) {
						if(response.responseText!=0 && response.responseText!="['0']"){
							var obj = Ext.decode(response.responseText);

							var menu = new Ext.menu.Menu();
							for(var j=0;j<obj.length;j++){

								menu.add(new Ext.Button({
									text:obj[j],
									width:'100%',
									questionType:j,
									handler: function(A,B){
										var uid = obj.id;
										var desktop = parent.QoDesk.App.getDesktop();
										
										var win = desktop.getWindow(thisObj.id_level + '_'+ A.questionType);
										var winWidth = desktop.getWinWidth();
										var winHeight = desktop.getWinHeight();
				
										if (!win) {
											win = desktop.createWindow({
												id : thisObj.id_level + '_'+ A.questionType,
												title : A.text,
												width : winWidth,
												height : winHeight,
												layout : 'fit',
												plain : false,
												listeners : {
													'show':function(x){
														var c = parent.document.getElementById('rd_'+thisObj.id_level);   
														c.src =  thisObj.config.AJAXPATH + "?controller=quiz_random&action=viewQuiz&subject_id_level="
																+ thisObj.id_level
																+ "&questionType="
																+ A.questionType
																+ "&uid="
																+ uid
																+ '&temp='
																+ Math.random();
													}
												},
												html : '<iframe id="rd_'+thisObj.id_level+'"  style="width:100%; height:'+(winHeight-30)+';" frameborder="no" border="0" marginwidth="0" marginheight="0">'
											});
										}
										win.show();
									}
								}));
							}
							//console.debug(Ext.getCmp('w_s_gp_l_tb'+domid));

							Ext.getCmp('w_s_gp_l_tb'+domid).add({								
								//iconCls : iconCls,
								text : tooltip,							
								menu:menu
							});
							Ext.getCmp('w_s_gp_l_tb'+domid).doLayout();
							//tb.add(menu);
						}
					},
					failure : function(response) {
						Ext.Msg.alert('failure', response.responseText);
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
	getSubjectCenter : function(domid) {
		var grid = this.getPaperList(domid + '_paperList');
		grid.region = 'center';

		var leftSide = new Ext.TabPanel({
					id : domid + '_left',
					activeTab : 0,
					width : 400,
					frame : true,
					items : [{
								title : il8n.Quiz_Paper_Result + il8n.curve,
								html : '<div id="' + domid + 'chart"></div>'
							}],
					region : 'east'
				});
		var layout = new Ext.Panel({
					layout : 'border',
					id : domid,
					items : [grid, leftSide]
				});

		return layout;
	},
	getMyQuizLine : function(chartid) {
		var so = new SWFObject(this.config.libPath + "am/amline/amline.swf",
				obj.id + "amline", "100%", "100%", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath + "am/amline/");
		so
				.addVariable(
						"settings_file",
						encodeURIComponent(this.config.AJAXPATH
								+ "?controller=subject&action=getMyQuizLine&id_level_subject_="
								+ this.id_level));
		so.write(chartid);
	}
});
