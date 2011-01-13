/**
 * 知识点管理的前台
 * */
wls.knowledge = Ext.extend(wls, {
	/**
	 * 必须依赖全局变量 user_,il8n
	 * 根据用户权限设置列表前的按钮
	 * */
	getList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=knowledge&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','id_level', 'name','weight','description']
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
		        },{
		             header: il8n.Weight
		            ,dataIndex: 'weight'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })
		        },{
		             header: il8n.Description
		            ,dataIndex: 'description'
		            ,hidden:true
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
			title:il8n.knowledge,
		    store: store,
		    cm: cm,        
		    id: domid,
		    width: 600,
		    height: 300,
		    clicksToEdit: 1,
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
							id:'w_u_g_l_i',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=knowledge&action=viewUpload' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1002'){
				tb.add({
					text: il8n.Export,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_u_g_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=knowledge&action=viewExport' width='100%' height='250' />"
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
							url:thisObj.config.AJAXPATH+"?controller=knowledge&action=delete",				
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
					url:thisObj.config.AJAXPATH+"?controller=knowledge&action=saveUpdate",				
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
});
