wls.subject = Ext.extend(wls, {
	getList:function(id){
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
		    columns: [{
		             header: il8n.Name
		            ,dataIndex: 'name'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })
		        }, {
		            header: il8n.ID,
		            dataIndex: 'id_level'
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
		        text: il8n.Import,
		        handler : function(){   
					var win = new Ext.Window({
						id:'w_u_g_l_i',
						layout:'fit',
						width:500,
						height:300,	
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=subject&action=viewUpload' width='100%' height='250' />"
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
						html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=subject&action=viewExport' width='100%' height='250' />"
					});
					win.show(this);
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
		store.load({params:{start:0, limit:8}});    
		return grid;
	}
});
