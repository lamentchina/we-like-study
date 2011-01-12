wls.quiz.paper = Ext.extend(wls.quiz, {
	
	 type:'paper'
	,id:null
	,paperData:null
	,time:{
		 start:null
		,stop:null
		,used:0
	}
	,ajaxIds:function(nextFunction){
		var thisObj = this;
		$.blockUI({
			message: '<h1>'+il8n.Paper.Paper+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_paper&action=getOne",
			data: {id:thisObj.id},					
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.paperData = obj.data[0];
				thisObj.questionsIds = obj.data[0].questions;	
				thisObj.state = 1;
				thisObj.addQuizBrief();
				thisObj.addNavigation();
				$.unblockUI();
				
				var objDate = new Date();
				var year = objDate.getFullYear();
				var month = objDate.getMonth() + 1;
				var day = objDate.getDate();
				var hour = objDate.getHours();
				var minute = objDate.getMinutes();
				var second = objDate.getSeconds();
				
				thisObj.time.start = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;
				eval(nextFunction);
			}
		});
	}
	,addQuizBrief:function(){
		var str = "<table width='90%'>" +
				"<tr>" +
				"<td>"+il8n.Name+"</td>" +
				"<td>"+this.paperData.title+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.TimeLimit+"</td>" +
				"<td>"+this.get_elapsed_time_string(this.paperData.time_limit)+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Score.Top+"</td>" +
				"<td>"+this.paperData.score_top+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Score.TopUser+"</td>" +
				"<td>"+this.paperData.score_top_user+"</td>" +	
				"</tr>" +	
				"<tr>" +
				"<td>"+il8n.Score.Avg+"</td>" +
				"<td>"+this.paperData.score_avg+"</td>" +	
				"</tr>" +	
				"<tr>" +
				"<td>"+il8n.Money+"</td>" +
				"<td>"+this.paperData.money+"</td>" +	
				"</tr>" +
				"<tr>" +
				"<td>"+il8n.Clock+"</td>" +
				"<td><span id='clock'>00</span></td>" +
				"</tr>"+
				"</table>";
		$("#paperBrief").append(str);
		var thisObj = this;
		Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Brief');
		wls_quiz_paper_clock = setInterval(function() {
			thisObj.time.used ++;
		   $('#clock').text(thisObj.get_elapsed_time_string(thisObj.time.used));
		}, 1000);
	}	
	,submit:function(nextFunction){
		$.blockUI({
			message: '<h1>'+il8n.Paper.Paper+il8n.Submit+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		
		window.clearInterval(wls_quiz_paper_clock);
		this.answersData = [];
		for(var i=0;i<this.questions.length;i++){
			this.answersData.push({
				 id:this.questions[i].id
				,answer:this.questions[i].getMyAnswer()
			});
		}
		var thisObj = this;
		
		var objDate = new Date();
		var year = objDate.getFullYear();
		var month = objDate.getMonth() + 1;
		var day = objDate.getDate();
		var hour = objDate.getHours();
		var minute = objDate.getMinutes();
		var second = objDate.getSeconds();
		
		thisObj.time.stop = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;		
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_paper&action=getAnswers",
			data: {answersData:thisObj.answersData,id:thisObj.id,time:thisObj.time},
			type: "POST",
			success: function(msg){
				$.unblockUI();
				var obj = thisObj.answersData = jQuery.parseJSON(msg);
				for(var i=0;i<obj.length;i++){
					thisObj.questions[i].answerData = obj[i];
				}
				eval(nextFunction);
			}
		});
	}	
	,get_elapsed_time_string:function(total_seconds){
		function pretty_time_string(num) {
			return ( num < 10 ? "0" : "" ) + num;
		}	
		var hours = Math.floor(total_seconds / 3600);
		total_seconds = total_seconds % 3600;
		
		var minutes = Math.floor(total_seconds / 60);
		total_seconds = total_seconds % 60;
		
		var seconds = Math.floor(total_seconds);
		
		  // Pad the minutes and seconds with leading zeros, if required
		hours = pretty_time_string(hours);
		minutes = pretty_time_string(minutes);
		seconds = pretty_time_string(seconds);
		
		  // Compose the string for display
		var currentTimeString = hours + ":" + minutes + ":" + seconds;
		
		return currentTimeString;
	}
	,getList:function(id){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=quiz_paper&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','name_subject','title', 'money']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: il8n.ID
		            ,width:30
		            ,dataIndex: 'id'
		        },{
		             header: il8n.Subject
		            ,dataIndex: 'name_subject'
		        }, {
		             header: il8n.Title
		            ,dataIndex: 'title'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }, {
		             header: il8n.Money
		            ,dataIndex: 'money'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }
		    ]
		});
		var grid = new Ext.grid.EditorGridPanel({
			title:il8n.Paper.Paper,
		    store: store,
		    cm: cm,        
		    id: id,
		    width: 600,
		    height: 300,
		    clicksToEdit: 2,
		    tbar: [{
		        text: il8n.Import,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_q_p_l_i',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewUpload&temp="+Math.random()+"' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
		        text: il8n.Export,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_l_e',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewExport&id="+Ext.getCmp(id).getSelectionModel().selection.record.id+"&temp="+Math.random()+"' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
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
						params:{id:Ext.getCmp(id).getSelectionModel().selection.record.id}				
					});					
				}
		    },{
		        text: il8n.DoQuiz,
		        handler : function(){
					window.open(thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewOne&id="+Ext.getCmp(id).getSelectionModel().selection.record.id);
				}
		    }],
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 8,
				displayInfo : true
			})
		});
		grid.on("afteredit", afteredit, grid);
		function afteredit(e){    
	        Ext.Ajax.request({				
				method:'POST',				
				url:thisObj.config.AJAXPATH+"?controller=quiz_paper&action=saveUpdate",				
				success:function(response){				
				    //Ext.Msg.alert('success',response.responseText);
				},				
				failure:function(response){				
				    Ext.Msg.alert('failure',response.responseText);
				},				
				params:{field:e.field,value:e.value,id:e.record.data.id}				
			});
	    }     
		store.load({params:{start:0, limit:8}});    
		return grid;
	}

});
var wls_quiz_paper_clock = null;
