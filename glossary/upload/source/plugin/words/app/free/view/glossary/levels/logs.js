/**
 * WLS,We-Like-Study,在线考试系统
 * 词汇本模块
 * 词汇本关卡子模块
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see www.welikestudy.com
 * */
wls.glossary.levels.logs = Ext.extend(wls.glossary.levels, {
	
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
			url : this.config.AJAXPATH+"?controller=glossary_levels_logs&action=getList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'time_joined', 'time_passed','subject','status','level','id_user','count_wrong','count_right','subject_name','username']
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
					}, {
						header :  il8n.user.user +"("+ il8n.normal.id +")",
						dataIndex : 'id_user',
						hidden : true 
					}, {
						header :  il8n.user.username ,
						dataIndex : 'username'
					},{
						header : il8n.subject.subject +"("+ il8n.normal.id +")",
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level'
					}, {
						header : il8n.glossary.time_passed,
						dataIndex : 'time_passed',
						renderer : function(v){
							var temp = v.substr(0,10);
							if(temp == '3000-01-01')return "<span style='color:red'>"+il8n.glossary.unpassed+"</span>"						
							return temp
						}
					}, {
						header : il8n.glossary.count_joined,
						dataIndex : 'time_joined',
						renderer : function(v){
							return v.substr(0,10);
						}
					}, {
						header : il8n.normal.status,
						dataIndex : 'status',
						renderer : function(v){
							if(v==0)return "<span style='color:red'>"+il8n.glossary.unjoined+"</span>";
							if(v==1)return il8n.glossary.joined;
							if(v==2)return il8n.glossary.passed;
						}
					}, {
						header : il8n.glossary.count_wrong,
						dataIndex : 'count_wrong'
					}, {
						header : il8n.glossary.count_right,
						dataIndex : 'count_right'
					}, {
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

		var grid = new Ext.grid.GridPanel({
			store : store,
			id : domid,
			cm : cm,

			width:'100%',
			height:500,
			loadMask : true,
			
			tbar : tb,
			bbar : bbar
		});

		store.load();
		
		return grid;
	},
	
	getMyGrid : function(domid){
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
			url : this.config.AJAXPATH+"?controller=glossary_levels_logs&action=getMyList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'time_joined', 'time_passed','subject','status','level','id_user','count_wrong','count_right','subject_name','username']
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
					}, {
						header :  il8n.user.user +"("+ il8n.normal.id +")",
						dataIndex : 'id_user',
						hidden : true 
					}, {
						header :  il8n.user.username ,
						dataIndex : 'username'
					},{
						header : il8n.subject.subject +"("+ il8n.normal.id +")",
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level'
					}, {
						header : il8n.glossary.time_passed,
						dataIndex : 'time_passed',
						renderer : function(v){
							var temp = v.substr(0,10);
							if(temp == '3000-01-01')return "<span style='color:red'>"+il8n.glossary.unpassed+"</span>"						
							return temp
						}
					}, {
						header : il8n.glossary.count_joined,
						dataIndex : 'time_joined',
						renderer : function(v){
							return v.substr(0,10);
						}
					}, {
						header : il8n.normal.status,
						dataIndex : 'status',
						renderer : function(v){
							if(v==0)return "<span style='color:red'>"+il8n.glossary.unjoined+"</span>";
							if(v==1)return il8n.glossary.joined;
							if(v==2)return il8n.glossary.passed;
						}
					}, {
						header : il8n.glossary.count_wrong,
						dataIndex : 'count_wrong'
					}, {
						header : il8n.glossary.count_right,
						dataIndex : 'count_right'
					}, {
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
	
		var grid = new Ext.grid.GridPanel({
			store : store,
			id : domid,
			cm : cm,
	
			width:'100%',
			height:500,
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
					if(access[i]=='305101'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var status = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.status;
								if(status==0 || status=='0'){
									alert(il8n.glossary.unjoined);
									return;
								}

								var level = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.level;
								var subject = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.subject;
								window.location.href = ("../../quiz.html?subject="+subject+"&level="+level); 
							}
						});
					}else if(access[i]=='305102'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var status = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.status;
								if(status!=0 && status!='0'){
									alert(il8n.glossary.joined);
									return;
								}
								var id = Ext.getCmp(domid).getSelectionModel().selections.items[0].id;
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH + "?controller=glossary_levels_logs&action=join",
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
										 id : id
									}
								});
							}
						});
					}else if(access[i]=='305103'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
									alert(il8n.normal.ClickCellInGrid);
									return;
								}
								var level = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.level;
								var subject = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.subject;
								window.location.href = ("../../grid.html?subjectid="+subject+"&level="+level); 
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
});