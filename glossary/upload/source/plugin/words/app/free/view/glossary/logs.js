/**
 * WLS,We-Like-Study,在线考试系统
 * 词汇本模块
 * 词汇本关卡子模块,词汇日志记录
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see www.welikestudy.com
 * */
wls.glossary.logs = Ext.extend(wls.glossary, {
	
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
			url : this.config.AJAXPATH+"?controller=glossary_logs&action=getList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'id_word', 'id_user','username','word','translation','logtime','count_right','count_wrong','subject','subject_name','level']
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
						header : il8n.glossary.word +"("+ il8n.normal.id +")",
						dataIndex : 'id_word',
						hidden : true 
					},{
						header : il8n.user.user +"("+ il8n.normal.id +")",
						dataIndex : 'id_user',
						hidden : true 
					},{
						header : il8n.subject.subject +"("+ il8n.normal.id +")",
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.user.username,
						dataIndex : 'username'
					}, {
						header : il8n.glossary.word,
						dataIndex : 'word'
					}, {
						header : il8n.glossary.translation,
						dataIndex : 'translation'
					}, {
						header : il8n.normal.logtime,
						dataIndex : 'logtime',
						hidden : true 
					}, {
						header : il8n.glossary.count_right,
						dataIndex : 'count_right'
					}, {
						header : il8n.glossary.count_wrong,
						dataIndex : 'count_wrong'
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level'
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
					if(access[i]=='305303'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								thisObj.deleteByIds(domid);
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
	
	deleteByIds : function(domid){
		var thisObj = this;
		console.debug();
		if (Ext.getCmp(domid).getSelectionModel().selections.items.length == 0) {
			alert(il8n.normal.ToDeleteClickCellFirst);
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
			url : thisObj.config.AJAXPATH + "?controller=glossary_logs&action=delete",
			success : function(response) {
				Ext.getCmp(domid).store.load({
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
				ids : ids
			}
		});
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
			url : this.config.AJAXPATH+"?controller=glossary_logs&action=getMyList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'id_word', 'id_user','username','word','translation','logtime','count_right','count_wrong','subject','subject_name','level']
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
						header : il8n.glossary.word +"("+ il8n.normal.id +")",
						dataIndex : 'id_word',
						hidden : true 
					},{
						header : il8n.user.user +"("+ il8n.normal.id +")",
						dataIndex : 'id_user',
						hidden : true 
					},{
						header : il8n.subject.subject +"("+ il8n.normal.id +")",
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.user.username,
						dataIndex : 'username',
						hidden : true 
					}, {
						header : il8n.glossary.word,
						dataIndex : 'word'
					}, {
						header : il8n.glossary.translation,
						dataIndex : 'translation'
					}, {
						header : il8n.normal.logtime,
						dataIndex : 'logtime',
						hidden : true 
					}, {
						header : il8n.glossary.count_right,
						dataIndex : 'count_right',
						hidden : true 
					}, {
						header : il8n.glossary.count_wrong,
						dataIndex : 'count_wrong',
						hidden : true 
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level',
						hidden : true 
					}, {
						header : il8n.subject.subject,
						dataIndex : 'subject_name',
						hidden : true 
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
					if(access[i]=='3103'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								//console.debug(domid);
								thisObj.deleteByIds(domid);
							}
						});
					}else if(access[i]=='3102'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls : iconCls,
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
											c.src =  "download.html";
										}
									},
									html : "<iframe id='paper_import' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
								});
								win.show(this);
							}
						});
					}else if(access[i]=='3104'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								location.href='quiz.html';
							}
						});
					}else if(access[i]=='3105'){
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
									url : thisObj.config.AJAXPATH + "?controller=glossary_logs&action=delete",
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

	ajaxQuestions : function(nextFunction) {
		var thisObj = this;
		Ext.Ajax.request({
			url : thisObj.config.AJAXPATH + "?controller=glossary_logs&action=getQuestions",
			method : "GET",
			success : function(response) {
				//console.debug(response.responseText);
				if(response.responseText=="Access denied"){
					Ext.Msg.alert("",il8n.normal.accessDenied);
					return;
				}
				var obj = Ext.decode(response.responseText);
				thisObj.questionsData = obj;
				thisObj.state = 2;

				Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Navigation');
				
				var str = 
					"<table width='90%' style='font-size:12px;'>" 

						+ "<tr>" 
							+ "<td>" + il8n.quiz.question + il8n.normal.total + "</td>" 
							+ "<td>" + thisObj.questionsData.length + "</td>"
						+ "</tr>" 

					"</table>";
				$("#paperBrief").append(str);
				//Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
				
				eval(nextFunction);
			}
		});
	},
	
	submit : function(nextFunction) {
		var thisObj = this;
		var answersData = [];
		var count_right = 0;
		
		for (var i = 0; i < thisObj.questions.length; i++) {
			var myAnswer = thisObj.questions[i].getMyAnswer();
			var correct = ( (myAnswer==thisObj.questions[i].answerData.answer)?1:0 );
			var ans = {
				 id : thisObj.questions[i].id
				,myAnswer : myAnswer
				,word : thisObj.questions[i].answerData.word
				,translation :  thisObj.questions[i].answerData.translation
				,correct : correct
			};
			
			if( myAnswer !='I_DONT_KNOW' ){
				if(correct)count_right++;
				answersData.push(ans);
			}
		}
		
		/*
		 * Ext JS can't POST 3-dimensional arrays to the server,
		 * while Jquery Can
		Ext.Ajax.request({
			url : thisObj.config.AJAXPATH + "?controller=glossary&action=submitQuiz",
			jsonData : data : {data:answersData,total:this.questions.length},
			method : "POST",
			success : function(msg) {
			}
		});
		*/
		
		var passed = 0;
		var msg = il8n.normal.total+":"+thisObj.questions.length+","								
				 +il8n.quiz.right+":"+count_right+","
				 +il8n.quiz.wrong+":"+(answersData.length-count_right);
		if(thisObj.questions.length!=answersData.length){
			msg += ","+il8n.quiz.giveup+":"+(thisObj.questions.length-answersData.length)+"<br/>";			
		}else{
				
		}
		msg += il8n.quiz.ratio+":"+ parseInt((count_right*100)/answersData.length);	
		
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=glossary_logs&action=submitQuiz",
			data : {data:answersData,total:this.questions.length},
			type : "POST",
			success : function(response) {
				Ext.Msg.alert("",msg);
			}
		});
	}
});