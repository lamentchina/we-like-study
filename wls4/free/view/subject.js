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
			id:"w_s_l_tb"
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
		    fields: ['id','title', 'questions']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [ {
		             header: il8n.ID
		            ,dataIndex: 'title'
		        },{
		             header: il8n.ID
		            ,dataIndex: 'id'
		            ,hidden:true
		        }, {
		             header: il8n.Count.Total
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
			id:"w_s_gp_l_tb"
		});		
		
		var grid = new Ext.grid.EditorGridPanel({
			//title:il8n.Subject+il8n.Paper.Paper,
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
				    //Ext.Msg.alert('success',response.responseText);
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
		var store = new Ext.data.JsonStore({
	        fields:['name', 'visits', 'views'],
	        data: [
	            {name:'Jul 07', visits: 245000, views: 3000000},
	            {name:'Aug 07', visits: 240000, views: 3500000},
	            {name:'Sep 07', visits: 355000, views: 4000000},
	            {name:'Oct 07', visits: 375000, views: 4200000},
	            {name:'Nov 07', visits: 490000, views: 4500000},
	            {name:'Dec 07', visits: 495000, views: 5800000},
	            {name:'Jan 08', visits: 520000, views: 6000000},
	            {name:'Feb 08', visits: 620000, views: 7500000}
	        ]
	    });
		
		
		var grid = this.getPaperList(domid+'_paperList');
		grid.region = 'center';
		var leftSide = new Ext.Panel({
			region:'east',
			width:300,
			layout: 'border',
	        items: [{
	        	region:'center',
	            xtype: 'linechart',
	            store: store,
	            xField: 'name',
	            yField: 'visits',
				listeners: {
					itemclick: function(o){
						var rec = store.getAt(o.index);
					}
				}
	        },new Ext.Button({text:'sadf',region:'south'})]

    	});
		var layout = new Ext.Panel({
			 layout: 'border'
			,id:domid
			,items: [grid,leftSide]			
		});
		



		return layout;
	}
});
