wls.user.privilege = Ext.extend(wls, {
	getList:function(id){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=user_privilege&action=jsonList',
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','id_level', 'name','money','ismenu']
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
		        }, {
		             header: il8n.Money
		            ,dataIndex: 'money'
	            	,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })
		        }, {
		             header: il8n.Menu
		            ,dataIndex: 'ismenu'
		            ,renderer:function(value){
		                if (value == 1) {
		                    return "是";
		                } else {
		                    return "否"
		                }
	            	}
		        }
		    ]
		});
		
		var grid = new Ext.grid.EditorGridPanel({
			title:il8n.Privilege,
		    store: store,
		    cm: cm,        
		    id: id,
		    width: 600,
		    height: 300,
		    clicksToEdit: 2,
		    loadMask:true,
		    tbar: [{
		        text: il8n.Import,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_g_l_i',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_privilege&action=viewUpload' width='100%' height='250' />"
					});
					win.show(this);
				}
		    },{
		        text: il8n.Export,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_g_l_e',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_privilege&action=viewExport' width='100%' height='250' />"
					});
					win.show(this);
				}
		    }],
		    bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 15,
				displayInfo : true
			})
		});
		grid.on("afteredit", afteredit, grid);
		function afteredit(e){    
	        Ext.Ajax.request({				
				method:'POST',				
				url:thisObj.config.AJAXPATH+"?controller=user_privilege&action=saveUpdate",				
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
});
