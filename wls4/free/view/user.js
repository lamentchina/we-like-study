wls.user = Ext.extend(wls, {
	getLogin:function(){
		
	},
	getList:function(id){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=user&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','username','password', 'money','credits']
		});
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: il8n.name
		            ,dataIndex: 'username'
		        }, {
		             header: il8n.password
		            ,dataIndex: 'password'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }, {
		             header: il8n.money
		            ,dataIndex: 'money'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }, {
		             header: il8n.credits
		            ,dataIndex: 'credits'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }
		    ]
		});
		var grid = new Ext.grid.EditorGridPanel({
		    store: store,
		    cm: cm,        
		    id: id,
		    width: 600,
		    height: 300,
		    clicksToEdit: 1,
		    tbar: [{
		        text: il8n.import,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_l_i',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user&action=viewUpload' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
		        text: il8n.export,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_l_e',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user&action=viewExport' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
		        text: il8n.delete,
		        handler : function(){
//		        	console.debug(id);
					console.debug();
			        Ext.Ajax.request({				
						method:'POST',				
						url:thisObj.config.AJAXPATH+"?controller=user&action=delete",				
						success:function(response){				
						    store.load();
						},				
						failure:function(response){				
						    Ext.Msg.alert('failure',response.responseText);
						},				
						params:{id:Ext.getCmp(id).getSelectionModel().selection.record.id}				
					});					
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
				url:thisObj.config.AJAXPATH+"?controller=user&action=saveUpdate",				
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
