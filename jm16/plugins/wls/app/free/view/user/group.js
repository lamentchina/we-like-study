wls.user.group = Ext.extend(wls, {
	getAddItemForm:function(){
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id:'w_u_g_ai_f',
			labelWidth: 75, 
	        frame:true,
	        bodyStyle:'padding:5px 5px 0',
	        width: 350,
	        defaults: {width: 100},
	        defaultType: 'textfield',	        
	
	        items: [{
	                fieldLabel: il8n.id_level,
	                width:150,
	                vtype:"alphanum",
	                name: 'id_level',
	                allowBlank:false
	            },{
	                fieldLabel: il8n.name,
	                width:150,
	                name: 'name',
	                allowBlank:false
	            }
	        ],
	
	        buttons: [{
		    	 text: il8n.save
		        ,handler:function(){
		    		var form = Ext.getCmp('w_u_g_ai_f').getForm();
	
		        	if(form.isValid()){
		    			$.blockUI({
		    				message: '<h1>'+il8n.loading+'......</h1>' 
		    			});  
		        		var obj = form.getValues();
		        		Ext.Ajax.request({				
		    				method:'POST',				
		    				url:thisObj.config.AJAXPATH+"?controller=user_group&action=addone&temp="+Math.random(),				
		    				success:function(response){				
		    					var obj = jQuery.parseJSON(response.responseText);
		    					$.unblockUI();
		    				},				
		    				failure:function(response){
		    					//TODO
		    					$.unblockUI();
		    				},				
		    				params:obj				
		    			});
		        	}else{
		        		 Ext.Msg.alert(il8n.fail,il8n.RequesttedImputMissing);
		        	}
		        }
        	}]
		});
		return form;
	}
	,getList : function(domid) {

		var thisObj = this;
		var store = new Ext.data.JsonStore({
			autoDestroy : true,
			url : thisObj.config.AJAXPATH
					+ '?controller=user_group&action=jsonList',
			root : 'data',
			idProperty : 'id',
			fields : ['id', 'id_level', 'name', 'count_user']
		});

		var cm = new Ext.grid.ColumnModel({
			defaults : {
				sortable : true
			},
			columns : [{
				header : il8n.id_level,
				dataIndex : 'id_level'
			}, {
				header : il8n.name,
				dataIndex : 'name',
				editor : new Ext.form.TextField({
					allowBlank : false
				})
			}, {
				header : il8n.count_total,
				dataIndex : 'count_user',
				editor : new Ext.form.TextField({
					allowBlank : false
				})
			}]
		});
		var tb = new Ext.Toolbar({
			id:"w_u_g_tb"
		});	
		var grid = new Ext.grid.EditorGridPanel({
			store : store,
			cm : cm,
			id : domid,
			width : 600,
			height : 300,
			clicksToEdit : 2,
			loadMask:true,
			tbar : tb,
			bbar : new Ext.PagingToolbar({
				store : store,
				pageSize : 15,
				displayInfo : true
			})
		});
		
		
		var privilege = user_.myUser.privilege.split(",");
		for(var i=0;i<privilege.length;i++){
			if(privilege[i]=='1301'){
				tb.add({
					text: il8n.importFile,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_u_g_l_i',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_group&action=viewUpload' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='130101'){
				tb.add({
					text: il8n.importFile+'(1)',
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_u_g_l_en',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_group&action=viewUploadOne' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1302'){
				tb.add({
					text: il8n.exportFile,
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_u_g_l_e',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_group&action=viewExport' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='130201'){
				tb.add({
					text: il8n.exportFile+'(1)',
			        handler : function(){   
						var win = new Ext.Window({
							id:'w_u_g_l_en',
							layout:'fit',
							width:500,
							height:300,	
							html: "<iframe src ='"+thisObj.config.AJAXPATH+"?controller=user_group&action=viewExportOne&id_level="+Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level+"' width='100%' height='250' />"
						});
						win.show(this);
					}
				});
			}else if(privilege[i]=='1303'){
				tb.add({
					text: il8n.deleteItems,
			        handler : function(){ 
						if(Ext.getCmp(domid).getSelectionModel().selection==null)return;//TODO
						Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=user_group&action=delete",				
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
			}else if(privilege[i]=='1304'){
				grid.on("afteredit", afteredit, grid);    
			}else if(privilege[i]=='130401'){
				tb.add({
				text : il8n.privilege,
				handler : function() {
					if(Ext.getCmp(domid).getSelectionModel().selection==null)return;//TODO
					var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_g_l_p_t',
						height : 300,
						width : 400,
						useArrows : true,
						autoScroll : true,
						animate : true,
						enableDD : false,
						containerScroll : true,
						rootVisible : false,
						frame : true,
						root : {
							nodeType : 'async',
							expanded : true
						},

						// auto create TreeLoader
						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=getPrivilege&id="
								+ id,
						buttons : [{
							text : il8n.submit,
							handler : function() {
								var checkedNodes = tree.getChecked();
								var s = "";
								for (var i = 0; i < checkedNodes.length; i++) {
									s += checkedNodes[i].attributes.id_level + ",";
								}
								Ext.getCmp("w_u_g_l_p_t").setVisible(false);
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=user_group&action=updatePrivilege",
									success : function(response) {
										Ext.getCmp("w_u_g_l_p_w").close();
									},
									failure : function(response) {
										Ext.Msg.alert('failure',
												response.responseText);
									},
									params : {
										 id : id
										,ids : s.substring(0,s.length-1)
									}
								});
							}
						}]

					});

					var win = new Ext.Window({
						id : 'w_u_g_l_p_w',
						layout : 'fit',
						title:il8n.Privilege,
						width : 500,
						height : 300,
						modal  :true,
						items : [tree]
					});
					win.show(this);

				}
			});
			}else if(privilege[i]=='130402'){
				tb.add({
				text : il8n.subject,
				handler : function() {
					var id = Ext.getCmp(domid).getSelectionModel().selection.record.data.id_level;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_g_l_s_t',
						height : 300,
						width : 400,
						useArrows : true,
						autoScroll : true,
						animate : true,
						enableDD : false,
						containerScroll : true,
						rootVisible : false,
						frame : true,
						root : {
							nodeType : 'async',
							expanded : true
						},

						// auto create TreeLoader
						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user_group&action=getSubject&id="
								+ id,
						buttons : [{
							text : il8n.submit,
							handler : function() {
								var checkedNodes = tree.getChecked();
								var s = "";
								for (var i = 0; i < checkedNodes.length; i++) {
									s += checkedNodes[i].attributes.id_level + ",";
								}
								Ext.getCmp("w_u_g_l_s_t").setVisible(false);
								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=user_group&action=updateSubject",
									success : function(response) {
										Ext.getCmp("w_u_g_l_s_w").close();
									},
									failure : function(response) {
										Ext.Msg.alert('failure',
												response.responseText);
									},
									params : {
										 id : id
										,ids : s.substring(0,s.length-1)
									}
								});
							}
						}]

					});

					var win = new Ext.Window({
								id : 'w_u_g_l_s_w',
								layout : 'fit',
								title:il8n.subject,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
							});
					win.show(this);

				}
			});
			}else if(privilege[i]=='1305'){
				tb.add({
					text: il8n.add,
			        handler : function(){   
						var form = thisObj.getAddItemForm();
						var w = new Ext.Window({
							title:il8n.addNewSubject,
					        width: 350,
					        height: 300,
					        layout: 'fit',
					        buttonAlign:'center',
					        items: [form],       
					        modal:true
					    });
						
						w.show();	
					}
				});	
			}	
		}
		
		grid.on("afteredit", afteredit, grid);
		function afteredit(e) {
			Ext.Ajax.request({
				method : 'POST',
				url : thisObj.config.AJAXPATH
						+ "?controller=user_group&action=saveUpdate",
				success : function(response) {
					// Ext.Msg.alert('success',response.responseText);
				},
				failure : function(response) {
					Ext.Msg.alert('failure', response.responseText);
				},
				params : {
					field : e.field,
					value : e.value,
					id : e.record.data.id
				}
			});
		}
		
		store.load({params : {start : 0,limit : 15}});
		return grid;
	}
});
