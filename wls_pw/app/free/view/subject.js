wls.subject = Ext.extend(wls, {
	id_level : null

	,
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
						fieldLabel : il8n.id_level,
						width : 150,
						vtype : "alphanum",
						name : 'id_level',
						allowBlank : false
					}, {
						fieldLabel : il8n.name,
						width : 150,
						name : 'name',
						allowBlank : false
					}, {
						fieldLabel : il8n.icon,
						width : 150,
						vtype : "alphanum",
						name : 'icon',
						allowBlank : false
					}, {
						fieldLabel : il8n.description,
						width : 150,
						name : 'description',
						allowBlank : true
					}, {
						fieldLabel : il8n.isknowledge,
						width : 150,
						name : 'isknowledge',
						maxLength : 1,
						regex:/^[10]/,
						allowBlank : false
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
										var obj = jQuery
												.parseJSON(response.responseText);
										$.unblockUI();
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
		{name: 'id_level'},
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
				editor: new Ext.form.TextField()
			},{
			    header: il8n.id_level, 
				width: 75, 
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
			}],
			autoExpandColumn: 'name'
		});
		var access = user_.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {
			if (access[i] == '190701') {
				tb.add({
					iconCls: 'bt_importFile',
					tooltip : il8n.importFile,
					handler : function() {
						var win = new Ext.Window({
							id : 'w_s_gp_l_i',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=subject&action=importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
						});
						win.show(this);
					}
				});
			} else if (access[i] == '190702') {
				tb.add({
					iconCls: 'bt_exportFile',
					tooltip : il8n.exportFile,
					handler : function() {
						var win = new Ext.Window({
							id : 'w_s_gp_l_e',
							layout : 'fit',
							width : 500,
							height : 300,
							html : "<iframe src ='"
									+ thisObj.config.AJAXPATH
									+ "?controller=subject&action=exportAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
						});
						win.show(this);
					}
				});
			} else if (access[i] == '190703') {
				tb.add({
					iconCls: 'bt_deleteItems',
					tooltip : il8n.deleteItems,
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
										store.load();
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
				tb.add({
							iconCls: 'bt_add',
							tooltip : il8n.add,
							handler : function() {
								var form = thisObj.getAddItemForm();
								var w = new Ext.Window({
											title : il8n.addNewSubject,
											width : 350,
											height : 300,
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
					tbar : tb,
					bbar : new Ext.PagingToolbar({
								store : store,
								pageSize : 15,
								displayInfo : true
							})
				});

		var access = user_.myUser.access.split(",");
		for (var i = 0; i < access.length; i++) {			
			if (access[i] == '1107') {
				tb.add({
					iconCls: 'bt_Quiz_Paper',
					tooltip : il8n.Quiz_Paper,
					handler : function() {
						if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
							alert(il8n.clickCellInGrid);
							return;
						}
						var items = Ext.getCmp(domid).getSelectionModel().selections.items;
						var pid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id;

						var uid = user_.myUser.id;
						var desktop = QoDesk.App.getDesktop();

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
								html : '<iframe src="'
										+ thisObj.config.AJAXPATH
										+ "?controller=quiz_paper&action=viewQuiz&id="
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
				user_.myUser.id + "amline", "100%", "100%", "8", "#FFFFFF");
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
