/**
 * 在线考试系统,WLS,We Like Study
 * 词汇表模块
 * 
 * 词汇表是"知识点练习"的一种实现方式,可以方便用户掌握科目的知识
 * 比如像CET4 CET6这样的语言考试科目
 * 或者像 政治,社会,财务 等专有名词非常多的科目
 * 都可以引用 词汇表 
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see http://www.welikestudy.com/forum.php?mod=viewthread&tid=1167
 * */
wls.glossary = Ext.extend(wls.quiz, {
	id_level : null	,
	type : 'glossary',
	id : null,
	paperData : null,
	subject : null,
	subjectid : null,
	level : null,
	passline: null,

	getAddItemForm : function() {
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

			items : [ {
				fieldLabel : il8n.glossary.word,
				width : 150,
				name : 'word',
				allowBlank : false
			}, {
				fieldLabel : il8n.glossary.translation,
				width : 150,
				name : 'translation',
				allowBlank : false
			}, {
				fieldLabel : il8n.subject.subject,
				width : 150,
				name : 'subject',
				allowBlank : false
			}],

			buttons : [{
				id : "glos_btn_add",
				text : il8n.normal.save,
				handler : function() {
					var form = Ext.getCmp('w_s_ai_f').getForm();

					if (form.isValid()) {
						Ext.getCmp('glos_btn_add').disable();
						var obj = form.getValues();
						Ext.Ajax.request({
									method : 'POST',
									url : ajaxPath
											+ "?controller=glossary&action=add&temp="
											+ Math.random(),
									success : function(response) {
										Ext.getCmp('glos_btn_add').enable();
										if(response.responseText==0){
											alert(il8n.normal.fail);
										}else{							
											alert(il8n.normal.done);			
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
	},	

	getGrid : function(domid){
		var thisObj = this;
		
		var searchVal = ' ';
		if(thisObj.subjectid!=null){
			searchVal += il8n.subject.id + "=" + thisObj.subjectid + " ";
		}
		if(thisObj.level!=null){
			searchVal += il8n.glossary.level + "=" + thisObj.level + " ";
		}
		searchVal = searchVal.trim();
		
		var search = new Ext.form.TextField({
			id : domid + '_search',
			width : 170,
			enableKeyEvents : true,
			value : searchVal
		});		
		
		search.on('keyup', function(a, b, c) {
			if (b.button == 12) {
				store.load({
					params : {
						start : 0,
						limit : 20,
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
			url : thisObj.config.AJAXPATH + "?controller=glossary&action=getList",
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'word', 'translation','subject_name','level','subject']
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
						header :  il8n.subject.id,
						dataIndex : 'subject',
						hidden : true 
					}, {
						header : il8n.glossary.word,
						dataIndex : 'word',
						editor : new Ext.form.TextField({
									allowBlank : false
								})
					}, {
						header : il8n.glossary.translation,
						dataIndex : 'translation',
						width : 250,
						editor : new Ext.form.TextField({
									allowBlank : false
								})
					}, {
						header : il8n.glossary.level,
						dataIndex : 'level',
						width : 250,
						editor : new Ext.form.TextField({
									allowBlank : false,
									regex:/^\d$/,
									maxLength:1
								})
					},{
						header : il8n.subject.subject,
						dataIndex : 'subject_name'
					}]
		});

		var bbar = new Ext.PagingToolbar({
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
				//console.debug(access);
				for(var i=0 ; i<access.length ; i++){
					//console.debug(access[i]);
					if(access[i]=='3001'){
						
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						//console.debug(1234213421341234);
						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_u_l_i',
									layout : 'fit',
									width : 500,
									height : 300,
									modal : true,
									html : "<iframe src ='"
											+ thisObj.config.AJAXPATH
											+ "?controller=glossary&action=importAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
									});
									win.show();
								}
							});
					}else if(access[i]=='3002'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");

						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {
								var win = new Ext.Window({
									id : 'w_u_l_i',
									layout : 'fit',
									width : 500,
									height : 300,
									modal : true,
									html : "<iframe src ='"
											+ thisObj.config.AJAXPATH
											+ "?controller=glossary&action=exportAll' width='100%' height='250' frameborder='no' border='0' marginwidth='0' marginheight='0' />"
									});
									win.show();
								}
							});
					}else if(access[i]=='3003'){
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
									url : thisObj.config.AJAXPATH + "?controller=glossary&action=delete",
									success : function(response) {
										store.load();
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
					}else if(access[i]=='3004'){
						grid.on("afteredit", function(e){
							Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH + "?controller=glossary&action=saveUpdate",
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
					}else if(access[i]=='3005'){
						eval("var iconCls = 'bt_'+obj.access2.p"+access[i]+"[1]+'_16_16';");
						eval("var tooltip = obj.access2.p"+access[i]+"[2];");
						//console.debug(access[i]);
						tb.add( {
							iconCls : iconCls,
							tooltip : tooltip,
							handler : function() {

								var form = addItem();
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
			url : thisObj.config.AJAXPATH + "?controller=glossary&action=getQuestions&subject="+thisObj.subject+"&level="+thisObj.level,
			method : "GET",
			success : function(response) {
				//console.debug(response.responseText);
				if(response.responseText=="Access denied"){
					Ext.Msg.alert("",il8n.normal.accessDenied);
					return;
				}
				var obj = Ext.decode(response.responseText);
				thisObj.questionsData = obj.questionsData;
				thisObj.state = 2;
				thisObj.passline = obj.levelData.passline;
				Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Navigation');
				
				var str = 
					"<table width='90%' style='font-size:12px;'>" 
						+ "<tr>" 
							+ "<td>" + il8n.glossary.passline + "</td>" 
							+ "<td>" + thisObj.passline + "</td>"
						+ "</tr>" 
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
				,level : thisObj.level
				,subject : thisObj.subject
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
				 +il8n.quiz.wrong+":"+(answersData.length-count_right)+"<br/>";
		if(thisObj.questions.length!=answersData.length){
			msg += ","+il8n.quiz.giveup+":"+(thisObj.questions.length-answersData.length)+"<br/>";			
		}else{
			if(parseInt((count_right*100)/answersData.length) >= thisObj.passline){
				passed = 1;
				msg += ","+il8n.glossary.passed+"<br/>";		
			}else{
				msg += ","+il8n.glossary.unpassed+"<br/>";		
			}
		}
		msg += il8n.quiz.ratio+":"+ parseInt((count_right*100)/answersData.length);	
		
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=glossary&action=submitQuiz",
			data : {data:answersData,total:this.questions.length},
			type : "POST",
			success : function(response) {
				Ext.Msg.alert("",msg);
			}
		});
		
		if(passed){
			$.ajax({
				url : thisObj.config.AJAXPATH + "?controller=glossary_levels_logs&action=passed",
				data : {subject:thisObj.subject,level:thisObj.level},
				type : "POST",
				success : function(response) {
					Ext.Msg.alert("",msg);
				}
			});
		}
	}
});