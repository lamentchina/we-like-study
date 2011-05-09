/**
 * WLS,We-Like-Study,在线考试系统
 * 词汇本模块
 * 词汇本关卡子模块
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see www.welikestudy.com
 * */
wls.glossary.levels = Ext.extend(wls.glossary, {
	
	level : null,
	subject : null,
	
	getGrid : function(domid){
		var thisObj = this;
		
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
											limit : 20
										}
									});
						}
					}, '-']
		});
		
		var store = new Ext.data.JsonStore({
			autoDestroy : true,
			url : this.config.AJAXPATH+"?controller=glossary_levels&action=getList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'level', 'subject','money','count_passed','count_joined','count_words','subject_name','passline']
		});

		store.on('beforeload', function() {
			Ext.apply(this.baseParams, {
						search : Ext.getCmp(domid + '_search').getValue()
					});
		});

		var cm = new Ext.grid.ColumnModel({
			defaults : {
				sortable : true
			},
			columns : [{
						header :  il8n.normal.id,
						dataIndex : 'id',
						hidden : true 
					},{
						header : il8n.subject.subject +"("+ il8n.normal.id +")",
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level'
					}, {
						header : il8n.user.money,
						dataIndex : 'money',
						editor : new Ext.form.TextField({
									allowBlank : false,
									regex:/^\d$/,
									maxLength:1
								})
					}, {
						header : il8n.glossary.passline,
						dataIndex : 'passline',
						editor : new Ext.form.TextField({
									allowBlank : false,
									regex:/[0-9]/,
									maxLength:3
								})
					}, {
						header : il8n.glossary.count_joined,
						dataIndex : 'count_joined'
					}, {
						header : il8n.glossary.count_words,
						dataIndex : 'count_words'
					}, {
						header : il8n.glossary.count_passed,
						dataIndex : 'count_passed'
					},{
						header : il8n.subject.subject,
						dataIndex : 'subject_name'
					}]
		});

		var bbar = new Ext.PagingToolbar({
			id:'pgtb',
			store : store,
			pageSize : 20,
			displayInfo : true
		});

		var grid = new Ext.grid.EditorGridPanel({
			store : store,
			id : domid,
			cm : cm,

			width:'100%',
			height:500,
			clicksToEdit : 2,
			loadMask : true,
			
			tbar : tb,
			bbar : bbar
		});

		store.load();

		//Get the current user's access , add some operable buttons to the toole bar 
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=user&action=getCurrentUserSession",
			success : function(response) {
				var obj = Ext.decode(response.responseText);
				//console.debug(obj);
				var access = obj.access;
				for(var i=0 ; i<access.length ; i++){
					if(access[i]=='305001'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ToDeleteClickCellFirst);
									return;
								}
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH + "?controller=glossary_levels&action=delete",
									success : function(response) {
										store.load({
											params : {
												start : (Ext.getCmp('pgtb').getPageData().activePage-1)*20,
												limit : 20
											}
										});
									},
									failure : function(response) {
										alert('Net connection failed');
									},
									params : {
										id : Ext.getCmp(domid).getSelectionModel().selection.record.id
									}
								});
							}
						});
					}else if(access[i]=='305002'){
						grid.on("afteredit", function(e){
							Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH + "?controller=glossary_levels&action=saveUpdate",
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
							, grid);					
					}else if(access[i]=='305003'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								var form = thisObj.addItem();
								var w = new Ext.Window({
											title : il8n.normal.append,
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
					}else if(access[i]=='305004'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selection == null) {
									alert(il8n.normal.ToDeleteClickCellFirst);
									return;
								}
								//console.debug(Ext.getCmp(domid).getSelectionModel().selection.record);
								var level = Ext.getCmp(domid).getSelectionModel().selection.record.data.level;
								var subject = Ext.getCmp(domid).getSelectionModel().selection.record.data.subject;
								window.location.href = ("../all.html?subjectid="+subject+"&level="+level); 
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
	},

	addItem : function(){
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
				fieldLabel : il8n.user.money,
				width : 150,
				regex:/^[0-9]/,
				name : 'money',
				allowBlank : false
			},new Ext.form.ComboBox({
				fieldLabel : il8n.subject.subject,
				name : 'subject',
				allowBlank : false,
				width : 150,
				hiddenName : 'subject',
				store : new Ext.data.SimpleStore({
					autoLoad : true,
					url :thisObj.config.AJAXPATH + "?controller=subject&action=getComboList",
					fields : ['subjectCode', 'subjectName']
				}), 
				valueField : 'subjectCode',// 域的值,对应于store里的fields
				displayField : 'subjectName',// 显示的域,对应于store里的fields
				typeAhead : true,// 设置true，完成自动提示
				mode : 'local', // 设置local，combox将从本地加载数据
				triggerAction : 'all',// 触发此表单域时,查询所有
				selectOnFocus : true,
				anchor : '90%',
				forceSelection : true
			}),{
				fieldLabel : il8n.glossary.passline,
				width : 150,
				regex:/^[0-9]/,
				name : 'passline',
				allowBlank : false
			}],

			buttons : [{
				text : il8n.normal.submit,
				id : 'g_l_a_b',
				handler : function() {
					var form = Ext.getCmp('w_s_ai_f').getForm();
					if (form.isValid()) {
						Ext.getCmp('g_l_a_b').disable();
						var obj = form.getValues();
						Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=glossary_levels&action=add&temp="
											+ Math.random(),
									success : function(response) {
										if(response.responseText==0){
											alert(il8n.normal.fail);
										}else{										
											Ext.getCmp('subject_add_win').close();
										}
									},
									failure : function(response) {

									},
									params : obj
								});
					} else {
						Ext.Msg.alert(il8n.normal.fail, il8n.normal.RequesttedImputMissing);
					}
				}
			}]
		});
		return form;
	}
});