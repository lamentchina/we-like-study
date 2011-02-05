wls.quiz.wrong = Ext.extend(wls.quiz, {
	
	 type:'wrong'
	,id_level_subject:null

	,ajaxIds:function(nextFunction){
		var thisObj = this;
		$.blockUI({
			message: '<h1>'+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=getOne",
			data: {id_level_subject:thisObj.id_level_subject},					
			type: "POST",
			success: function(msg){

				thisObj.questionsIds = msg;	
				var temp = jQuery.parseJSON('['+msg+']');
				thisObj.count.total = temp.length;
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
				"<td>"+il8n.count_total+"</td>" +
				"<td>"+this.count.total+"</td>" +	
				"</tr>" +

				"</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
	}	
	,submit:function(nextFunction){
		$.blockUI({
			message: '<h1>'+il8n.submit+'</h1><br/>'+il8n.loading+'......' 
		}); 
		
		this.answersData = [];
		for(var i=0;i<this.questions.length;i++){
			this.answersData.push({
				 id:this.questions[i].id
				,answer:this.questions[i].getMyAnswer()
			});
		}
		var thisObj = this;
	
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=getAnswers",
			data: {answersData:thisObj.answersData,id_level_subject:thisObj.id_level_subject},
			type: "POST",
			success: function(msg){
				$.unblockUI();
				var obj = thisObj.answersData = jQuery.parseJSON(msg);
				for(var i=0;i<obj.length;i++){
					thisObj.questions[i].answerData = obj[i];
				}
				eval(nextFunction);
				thisObj.showResult();
			}
		});
	}
	
	,showResult:function(){
		var str = "<table width='90%'>" +			
				"<tr>" +
				"<td>"+il8n.count_right+"</td>" +
				"<td>"+this.count.right+"</td>" +	
				"</tr>" +				
				"<tr>" +
				"<td>"+il8n.count_wrong+"</td>" +
				"<td>"+this.count.wrong+"</td>" +	
				"</tr>" +				
				"<tr>" +
				"<td>"+il8n.count_giveUp+"</td>" +
				"<td>"+this.count.giveup+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.count_total+"</td>" +
				"<td>"+this.count.total+"</td>" +	
				"</tr>" +
				"</table>";
		var ac = Ext.getCmp('ext_Operations');		
		ac.layout.activeItem.collapse(false);		
		ac.add({
	    	id:'ext_wrongResult',
	        title: il8n.Paper.Result,
	        html: '<div id="wrongresult">aaa</div>'			
		});
		ac.doLayout();
		
		$("#wrongresult").empty();
		$("#wrongresult").append(str);
		
		$.blockUI({
			message: str 
		}); 
		 $('.blockOverlay').attr('title','Click to unblock').click($.unblockUI); 
	}
	,getList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=quiz_wrong&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','id_level_subject','id_quiz_paper','date_created','timedif','subject_name','count','id_user']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: il8n.id
		            ,dataIndex: 'id'
		        },{
		             header: il8n.subject
		            ,dataIndex: 'id_level_subject'
		            ,hidden:true
		        },{
		             header: il8n.id_paper
		            ,dataIndex: 'id_quiz_paper'
		            ,hidden:true
		        },{
		             header: il8n.date_created
			        ,dataIndex: 'date_created'
			        ,hidden:true
			    },{
		             header: il8n.date_created
			        ,dataIndex: 'timedif'
			    },{
		             header: il8n.subject
			        ,dataIndex: 'subject_name'
			    },{
		             header: il8n.count_wrong
			        ,dataIndex: 'count'
			    },{
		             header: il8n.user
			        ,dataIndex: 'id_user'
			    }
		    ]
		});
		
		var tb = new Ext.Toolbar({
			id:"w_q_w_l_tb"+domid
		});	
		
		var grid = new Ext.grid.GridPanel({
		    store:store,
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
			if(privilege[i]=='115303'){
				tb.add({
					text: il8n.deleteItems,
			        handler : function(){   
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=quiz_paper&action=delete",				
							success:function(response){				
							    store.load();
							},				
							failure:function(response){				
							    //TODO Ext.Msg.alert('failure',response.responseText);
							},				
							params:{id:Ext.getCmp(domid).getSelectionModel().selection.record.id}				
						});		
					}
				});
			}
		}		   
		store.load({params:{start:0, limit:15}});    
		return grid;
	}
	,getMyList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=quiz_wrong&action=myList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','id_level_subject','id_quiz_paper','date_created','timedif','subject_name','count','id_user']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: il8n.id
		            ,dataIndex: 'id'
		        },{
		             header: il8n.subject
		            ,dataIndex: 'id_level_subject'
		            ,hidden:true
		        },{
		             header: il8n.id_paper
		            ,dataIndex: 'id_quiz_paper'
		            ,hidden:true
		        },{
		             header: il8n.date_created
			        ,dataIndex: 'date_created'
			        ,hidden:true
			    },{
		             header: il8n.date_created
			        ,dataIndex: 'timedif'
			    },{
		             header: il8n.subject
			        ,dataIndex: 'subject_name'
			    },{
		             header: il8n.count_wrong
			        ,dataIndex: 'count'
			    },{
		             header: il8n.user
			        ,dataIndex: 'id_user'
			        ,hidden:true
			    }
		    ]
		});
		
		var tb = new Ext.Toolbar({
			id:"w_q_w_ml_tb"+domid
		});	
		
		var grid = new Ext.grid.GridPanel({
		    store:store,
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
			if(privilege[i]=='125003'){
				tb.add({
					text: il8n.deleteItems,
			        handler : function(){   
			        	var ids = '';
			        	var items = Ext.getCmp(domid).getSelectionModel().selections.items;
			        	for(var i=0;i<items.length;i++){
			        		ids += items[i].data.id + ',';
			        	}
			        	ids = ids.substring(0,ids.length-1);
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=delete",				
							success:function(response){				
							    store.load();
							},				
							failure:function(response){				
							    Ext.Msg.alert('failure',response.responseText);
							},				
							params:{ids:ids}				
						});		
					}
				});
			}else if(privilege[i]=='125007'){
				tb.add({
			        text: il8n.Quiz_Wrongs,
			        handler : function(){
						var sid = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id_level_subject;
						var uid = user_.myUser.id;
						var desktop = QoDesk.App.getDesktop();
						
						var win = desktop.getWindow(sid+'_qdesk');							
						var winWidth = desktop.getWinWidth();
						var winHeight = desktop.getWinHeight();
						
						if(!win){
							win = desktop.createWindow({
								id:sid+'_qdesk',
								title: il8n.Quiz_Wrongs,
								width: winWidth,
								height: winHeight,
								layout: 'fit',
								plain:false,
								html:'<iframe src="'+thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=viewOne&id_level_subject="+sid+"&uid="+uid+'&temp='+Math.random()+'" style="width:100%; height:100%;" frameborder="no" border="0" marginwidth="0" marginheight="0">'
							});
						}
						win.show();				        	
					}
			    });   
			}
		}		   
		store.load({params:{start:0, limit:15}});    
		return grid;
	}

});
