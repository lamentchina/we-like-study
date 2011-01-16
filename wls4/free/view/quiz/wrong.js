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
				"<td>"+il8n.Count.Total+"</td>" +
				"<td>"+this.count.total+"</td>" +	
				"</tr>" +

				"</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
	}	
	,submit:function(nextFunction){
		$.blockUI({
			message: '<h1>'+il8n.Submit+'</h1><br/>'+il8n.Wait+'......' 
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
				
				//console.debug(nextFunction);
				eval(nextFunction);
				thisObj.showResult();
			}
		});
	}
	
	,showResult:function(){

		var str = "<table width='90%'>" +			
				"<tr>" +
				"<td>"+il8n.Count.Right+"</td>" +
				"<td>"+this.count.right+"</td>" +	
				"</tr>" +				
				"<tr>" +
				"<td>"+il8n.Count.Wrong+"</td>" +
				"<td>"+this.count.wrong+"</td>" +	
				"</tr>" +				
				"<tr>" +
				"<td>"+il8n.Count.GiveUp+"</td>" +
				"<td>"+this.count.giveup+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Count.Total+"</td>" +
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
		    fields: ['id','id_level_subject','id_quiz_paper','date_created','timedif','subject_name','count']
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
		            ,hidden:true
		        },{
		             header: il8n.Paper.Paper
		            ,dataIndex: 'id_quiz_paper'
		            ,hidden:true
		        },{
		             header: il8n.DateCreated
			        ,dataIndex: 'date_created'
			        ,hidden:true
			    },{
		             header: il8n.DateCreated
			        ,dataIndex: 'timedif'
			    },{
		             header: il8n.Subject
			        ,dataIndex: 'subject_name'
			    },{
		             header: il8n.Count.Wrong
			        ,dataIndex: 'count'
			    }
		    ]
		});
		
		var tb = new Ext.Toolbar({
			id:"w_s_l_tb"
		});	
		
		var grid = new Ext.grid.GridPanel({
			title:il8n.Wrongs,
		    store:store,
		    cm: cm,        
		    id: domid,
		    width: 600,
		    height: 300,
		    tbar: tb,
		    tbar: [
		    {
		        text: il8n.Export,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_q_w_l_e',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=viewExport&id="+Ext.getCmp(domid).getSelectionModel().selection.record.id+"&temp="+Math.random()+"' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
		        text: il8n.Delete,
		        handler : function(){
		        	var ids = '';
		        	var arr = Ext.getCmp(domid).getSelectionModel().selections.keys;
		        	for(var i=0;i<arr.length;i++){
		        		ids += arr[i]+',';
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
		    },{
		        text: il8n.DoQuiz,
		        handler : function(){
		        	var id_level_subject = Ext.getCmp(domid).getSelectionModel().selections.items[0].data.id_level_subject;
		        	console.debug(id_level_subject);
					window.open(thisObj.config.AJAXPATH+"?controller=quiz_wrong&action=viewOne&id_level_subject="+id_level_subject);
				}
		    }],
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 8,
				displayInfo : true
			})
		});
		var privilege = user_.myUser.privilege.split(",");
		for(var i=0;i<privilege.length;i++){
			if(privilege[i]=='1101'){
				tb.add({
					text: il8n.Import,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_q_p_l_i',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewUpload' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1102'){
				tb.add({
					text: il8n.Export,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_q_p_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewExport' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1103'){
				tb.add({
					text: il8n.Delete,
			        handler : function(){   
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=quiz_paper&action=delete",				
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
			}else if(privilege[i]=='1107'){
				tb.add({
			        text: il8n.DoQuiz,
			        handler : function(){
						window.open(thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewOne&id="+Ext.getCmp(domid).getSelectionModel().selection.record.id);
					}
			    });   
			}
		}
		   
		store.load({params:{start:0, limit:8}});    
		return grid;
	}

});
