wls.quiz.log = Ext.extend(wls.quiz, {
	
	 type:'log'
	,id:null
	,logData:null
	,ajaxIds:function(nextFunction){
		var thisObj = this;
		$.blockUI({
			message: '<h1>'+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_log&action=getOne",
			data: {id:thisObj.id},					
			type: "POST",
			success: function(msg){
				
				var obj = jQuery.parseJSON(msg);
				thisObj.logData = obj;
				thisObj.questionsIds = obj.id_question;	
				thisObj.state = 1;
				thisObj.addQuizBrief();
				thisObj.addNavigation();
				$.unblockUI();
				
				eval(nextFunction);				
			}
		});
	}
	,addQuizBrief:function(){
		var str = "<table width='90%'>" +
				"<tr>" +
				"<td width='50%'>"+il8n.DateCreated+"</td>" +
				"<td width='50%'>"+this.logData.date_created+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Score.Total+"</td>" +
				"<td>"+this.logData.cent+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Score.Score+"</td>" +
				"<td>"+this.logData.mycent+"</td>" +	
				"</tr>" +	
				"<tr>" +
				"<td>"+il8n.Count.Right+"</td>" +
				"<td>"+this.logData.count_right+"</td>" +	
				"</tr>" +	
				"<tr>" +
				"<td>"+il8n.Count.Wrong+"</td>" +
				"<td>"+this.logData.count_wrong+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Proportion+"</td>" +
				"<td>"+this.logData.proportion+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Application+"</td>" +
				"<td>"+this.logData.application+"</td>" +	
				"</tr>" +				
				"</table>";
		$("#paperBrief").append(str);
	}	
	,submit:function(nextFunction){
		if(this.state==4)return;
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_log&action=getAnswers",
			data: {id:thisObj.id},
			type: "POST",
			success: function(msg){
				thisObj.state = 4;
				var obj = thisObj.answersData = jQuery.parseJSON(msg);
				for(var i=0;i<obj.length;i++){
					thisObj.questions[i].answerData = obj[i];
					thisObj.questions[i].setMyAnser();
				}
				thisObj.addDescriptions();
				eval(nextFunction);
			}
		});
	}
	
	,getList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=quiz_log&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','date_created','id_level_subject','id_user', 'cent','mycent','count_right','count_wrong','count_giveup','count_total']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: il8n.ID
		            ,dataIndex: 'id'
		        },{
		             header: il8n.Subject
		            ,dataIndex: 'id_level_subject'
		        }
		    ]
		});
		
		var tb = new Ext.Toolbar({
			id:"w_q_lg_l_tb"
		});	
		
		var grid = new Ext.grid.GridPanel({
		    store: store,
		    cm: cm,        
		    id: domid,
		    width: 600,
		    height: 300,
		    tbar: tb,
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 15,
				displayInfo : true
			})
		});
		
		var privilege = user_.myUser.privilege.split(",");
		for(var i=0;i<privilege.length;i++){
			if(privilege[i]=='115101'){
				tb.add({
					text: il8n.Import,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_q_p_l_i',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_log&action=viewUpload' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='115102'){
				tb.add({
					text: il8n.Export,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_q_p_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_log&action=viewExport' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='115103'){
				tb.add({
					text: il8n.Delete,
			        handler : function(){   
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=quiz_log&action=delete",				
							success:function(response){				
							    store.load();
							},				
							failure:function(response){				
							    Ext.Msg.alert('failure',response.responseText);
							},				
							params:{id:Ext.getCmp(domid).getSelectionModel().selection.record.id}				
						});		
					}
				});
			}else if(privilege[i]=='115107'){
				tb.add({
			        text: il8n.Review+il8n.Log,
			        handler : function(){
						window.open(thisObj.config.AJAXPATH+"?controller=quiz_log&action=viewOne&id="+Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id);
					}
			    });   
			}
		}
  
		store.load({params:{start:0, limit:8}});    
		return grid;
	}

});
