sm.user= {
	list:function(){
		var store = new Ext.data.JsonStore( {
				autoDestroy : true,
				proxy : new Ext.data.HttpProxy( {
					url : url.sm_user_list,
					method : 'GET'
				}),
				root : 'data',
				idProperty : 'id',
				fields : [ 'id' , 'tradeNo' , 'name' , 'date_created' , 'user_created' , 'state' , 'type' , 'amount' ]
			});	
			
			var cm = new Ext.grid.ColumnModel( {
				defaults : {
					sortable : true
				},
				columns : [ 
					 { header : il8n.id , dataIndex : 'id' }
					,{ header : il8n.tradeNo , dataIndex : 'tradeNo' }
					,{ header : il8n.name , dataIndex : 'name' }
					,{ header : il8n.date_created , dataIndex : 'date_created' }
					,{ header : il8n.user_created , dataIndex : 'user_created' }
					,{ header : il8n.state , dataIndex : 'state' }
					,{ header : il8n.type , dataIndex : 'type' }
					,{ header : il8n.amount , dataIndex : 'amount' }
				 ]
			});
		
			var search = new Ext.form.TextField({
				id : 'p_h_l_search',
				width : 170,
				enableKeyEvents : true
			});		
		
			search.on('keyup', function(a, b, c) {
				if (b.button == 12) {
					store.load({
						params : {
							start : 0,
							limit : 20,
							search : Ext.getCmp('p_h_l_search').getValue()
						}
					});
				}
			});
			
			var tb = new Ext.Toolbar({
				items : [ search, {
					text : il8n.search,
					handler : function() {
						store.load({
									params : {
										start : 0,
										limit : 20
									}
								});
					}
				},'-',{
					 text : il8n.detail
					,handler : function() {
						if (Ext.getCmp('p_h_list').getSelectionModel().selections.items.length == 0) {
							alert(il8n.error_rowSelectedFirst);
							return;
						}
						var row_id = Ext.getCmp('p_h_list').getSelectionModel().selections.items[0].data.id;
						var tabPanel = po.head.detail(row_id);

						var w = new Ext.Window({
							title : il8n.detail,
							id : 'p_h_l_win_detail_'+row_id,
							width : '80%',
							height : 450,
							layout : 'fit',
							items : [tabPanel]
						});
		
						w.show();
					}
				}]
			});
		
			store.on('beforeload', function() {
				Ext.apply(this.baseParams, {
					search : Ext.getCmp('p_h_l_search').getValue()
				});
			});
		
			var grid = new Ext.grid.GridPanel( {
				id : 'p_h_list',
				store : store,
				cm : cm,
				height : 350,
				width : '95%',
				tbar : tb,
				loadMask : {  
					msg : il8n.loading
				} ,
				bbar : new Ext.PagingToolbar( {
					store : store,
					pageSize : 15,
					displayInfo : true
				})
			});
		
			store.load();
			return grid;
	}
}