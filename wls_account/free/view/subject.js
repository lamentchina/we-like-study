wls.subject = Ext.extend(wls, {
	id_level:null
	
	/**
	 * 必须依赖全局变量 user_,il8n
	 * 根据用户权限设置列表前的按钮
	 * */
	,getList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=subject&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','id_level', 'name']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [ {
		            header: il8n.ID,
		            dataIndex: 'id_level'
		        },{
		             header: il8n.Name
		            ,dataIndex: 'name'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })
		        }
		    ]
		});		

		var tb = new Ext.Toolbar({
			id:"w_s_l_tb"+domid
		});		
		
		var grid = new Ext.grid.EditorGridPanel({
			title:il8n.Subject,
		    store: store,
		    cm: cm,        
		    id: domid,
		    width: 600,
		    height: 300,
		    clicksToEdit: 2,
		    tbar: tb,
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 15,
				displayInfo : true
			})
		});		
		
		var privilege = user_.myUser.privilege.split(",");
		for(var i=0;i<privilege.length;i++){
			if(privilege[i]=='1001'){
				tb.add({
					text: il8n.Import,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_s_gp_l_i',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=subject&action=viewUpload' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1002'){
				tb.add({
					text: il8n.Export,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_s_gp_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=subject&action=viewExport' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1003'){
				tb.add({
					text: il8n.Delete,
			        handler : function(){   
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=subject&action=delete",				
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
			}else if(privilege[i]=='1004'){
				grid.on("afteredit", afteredit, grid);    
			}else if(privilege[i]=='1005'){
				//TODO
			}
			function afteredit(e){    
		        Ext.Ajax.request({				
					method:'POST',				
					url:thisObj.config.AJAXPATH+"?controller=subject&action=saveUpdate",				
					success:function(response){				
					    //Ext.Msg.alert('success',response.responseText);
					},				
					failure:function(response){				
					    Ext.Msg.alert('failure',response.responseText);
					},				
					params:{field:e.field,value:e.value,id:e.record.data.id}				
				});
		    } 
		}		
		
		store.load({params:{start:0, limit:15}});    
		return grid;
	}
	
	,getPaperList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=subject&action=getPaperList&id_level_subject='+thisObj.id_level,
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','title', 'questions','count_used','money','score_avg','score_top','score_top_user','time_limit']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [ {
		             header: il8n.ID
		            ,dataIndex: 'id'
		            ,width:50
		        },{
		             header: '标题'
		            ,dataIndex: 'title'
		        },{
		             header: '使用次数'
		            ,dataIndex: 'count_used'
		            ,hidden:true
		        },{
		             header: '金币'
		            ,dataIndex: 'money'
		        },{
		             header: '平均分'
		            ,dataIndex: 'score_avg'
		            ,hidden:true
		        },{
		             header: '最高分'
		            ,dataIndex: 'score_top'
		            ,hidden:true
		        }, {
		             header: '题目总数'
		            ,dataIndex: 'questions'
		            ,renderer:function(value){
		            	var json = '['+value+']';
		            	var arr = jQuery.parseJSON(json);
						return arr.length;
					}
		        }
		    ]
		});		

		var tb = new Ext.Toolbar({
			id:"w_s_gp_l_tb"+domid
		});		
		
		var grid = new Ext.grid.EditorGridPanel({
		    store: store,
		    cm: cm,        
		    id: domid,

		    clicksToEdit: 2,
		    tbar: tb,
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 15,
				displayInfo : true
			})
		});		
				
		var privilege = user_.myUser.privilege.split(",");
		for(var i=0;i<privilege.length;i++){
			if(privilege[i]=='1102'){
				tb.add({
					text: il8n.Export,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_s_gp_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=subject&action=viewExport' width='100%' height='250' />"
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
							url:thisObj.config.AJAXPATH+"?controller=subject&action=delete",				
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
			}else if(privilege[i]=='1104'){
				grid.on("afteredit", afteredit, grid);    
			}else if(privilege[i]=='1107'){
				tb.add({
			        text: il8n.DoQuiz,
			        handler : function(){
						window.open(thisObj.config.AJAXPATH+"?controller=quiz_paper&action=viewOne&id="+Ext.getCmp(domid).getSelectionModel().selection.record.id);
					}
			    }); 
			}
		}
		function afteredit(e){    
	        Ext.Ajax.request({				
				method:'POST',				
				url:thisObj.config.AJAXPATH+"?controller=quiz_paper&action=saveUpdate",				
				success:function(response){				
				    //TODO
				},				
				failure:function(response){				
				    Ext.Msg.alert('failure',response.responseText);
				},				
				params:{field:e.field,value:e.value,id:e.record.data.id}				
			});
	    } 		
		store.load({params:{start:0, limit:15}});    
		return grid;
	}
	,getSubjectCenter:function(domid){		
		var grid = this.getPaperList(domid+'_paperList');
		grid.region = 'center';		
		
		var leftSide = new Ext.TabPanel({
			 id:domid+'_left'
	        ,activeTab: 0
	        ,width:400
	        ,frame:true
	        ,items:[{
	        	title:'对错率曲线'
	        	,html:'<div id="'+domid+'chart"></div>'	
	        }]
	        ,region:'east'
    	});
		var layout = new Ext.Panel({
			 layout: 'border'
			,id:domid
			,items: [grid,leftSide]			
		});

		return layout;
	}
	,getMyQuizLine:function(chartid){	
		var so = new SWFObject(this.config.libPath+"am/amline/amline.swf", user_.myUser.id+"amline", "100%", "100%", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath+"am/amline/");
		so.addVariable("settings_file", encodeURIComponent(this.config.AJAXPATH+"?controller=subject&action=getMyQuizLine&id_level_subject_="+this.id_level));	
		so.write(chartid);		
	}
});
