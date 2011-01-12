wls.user = Ext.extend(wls, {
	
	myUser:{
		privilege:null,
		group:null,
		subject:null,
		username:null,
		id:null,
		money:null,
		credits:null	
	},

	
	/**
	 * 添加登录框.
	 * 如果登录成功,页面会重刷新
	 * 
	 * @return Ext.form.FormPanel
	 * */
	getLogin:function(){
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id:'wls_user_login_form',
			labelWidth: 75, // label settings here cascade unless overridden
	        frame:true,
	        title: il8n.User.Login,
	        bodyStyle:'padding:5px 5px 0',
	        width: 350,
	        defaults: {width: 100},
	        defaultType: 'textfield',	        
	
	        items: [{
	                fieldLabel: il8n.User.Name,
	                name: 'username',
	                allowBlank:false
	            },{
	                fieldLabel: il8n.User.PassWord,
	                name: 'password',
	                inputType:'password',
	                allowBlank:false
	            },{
	                fieldLabel: il8n.CheckCAPTCHA,
	                enableKeyEvents:true,
	                name: 'CAPTCHA',
	                allowBlank:false,
	                id:'CAPTCHA'
	            }, new Ext.BoxComponent({
	            	fieldLabel: il8n.CAPTCHA,
	                height: 32, // give north and south regions a height
	                autoEl: {
	                    tag: 'div',
	                    html:'<img style="width:100px; height:28px;" id="captcha" src="'+thisObj.config.CAPTCHA+'securimage_show.php" alt="CAPTCHA Image" />'
	                }
	            })
	        ],

	        buttons: [{
			    	 text: il8n.User.Login
			        ,handler:function(){
			        	thisObj.login();
			        }
	        	},{
			    	 text: il8n.ReCAPTCHA
			        ,handler:function(){
			        	$('#captcha').attr("src",thisObj.config.CAPTCHA+'securimage_show.php?wlstemp='+Math.random());
			        }
	        	}
	        ]
		});
		Ext.getCmp('CAPTCHA').on('keyup', function(obj,e) {
			if(e.getKey()=='13'){
				thisObj.login();
			}
		});
		return form;
	},
	login:function(){
		$.blockUI({
			message: '<h1>'+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		var thisObj = this;
		var form = Ext.getCmp('wls_user_login_form').getForm();
    	if(form.isValid()){
    		var obj = form.getValues();
    		Ext.Ajax.request({				
				method:'POST',				
				url:thisObj.config.AJAXPATH+"?controller=user&action=login",				
				success:function(response){				
					var obj = jQuery.parseJSON(response.responseText);
					if(obj.msg=='ok'){
						location.reload();
					}
					$.blockUI({
						message: '<h1>'+il8n.Error+'</h1>' 
					}); 					
					setTimeout($.unblockUI, 2000); 
				    //Ext.Msg.alert('success',response.responseText);
					
					$('#captcha').attr("src",thisObj.config.CAPTCHA+'securimage_show.php?wlstemp='+Math.random());
				},				
				failure:function(response){	
					$.unblockUI();
				    Ext.Msg.alert('failure',response.responseText);
				    $('#captcha').attr("src",thisObj.config.CAPTCHA+'securimage_show.php?wlstemp='+Math.random());
				},				
				params:obj				
			});
    	}
	},
	getList:function(domid){
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
		             header: il8n.Name
		            ,dataIndex: 'username'
		        }, {
		             header: il8n.PassWord
		            ,dataIndex: 'password'
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
		             header: il8n.Credits
		            ,dataIndex: 'credits'
		            ,editor: new Ext.form.TextField({
	                    allowBlank: false
	                })  
		        }
		    ]
		});
		var grid = new Ext.grid.EditorGridPanel({
		    store: store,
		    title:il8n.User.User,
		    cm: cm,        
		    id: domid,
		    width: 600,
		    height: 300,
		    clicksToEdit: 2,
		    loadMask:true,
		    tbar: [{
		        text: il8n.Import,
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
		        text: il8n.Export,
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
		        text: il8n.Delete,
		        handler : function(){
			        Ext.Ajax.request({				
						method:'POST',				
						url:thisObj.config.AJAXPATH+"?controller=user&action=delete",				
						success:function(response){				
						    store.load();
						},				
						failure:function(response){				
						    Ext.Msg.alert('failure',response.responseText);
						},				
						params:{id:Ext.getCmp(domid).getSelectionModel().selection.record.id}				
					});					
				}
		    },{
		        text: il8n.User.Group,
		        handler : function(){
			     	var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
	
					var tree = new Ext.tree.TreePanel({
						id:'u_l_g_t',
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
								+ "?controller=user&action=getGroup&username="
								+ username,
						buttons : [{
							text : il8n.Submit,
							handler : function() {
								var checkedNodes = tree.getChecked();
								var s = "";
								for (var i = 0; i < checkedNodes.length; i++) {
									s += checkedNodes[i].attributes.id_level + ",";
								}
								Ext.getCmp("u_l_g_t").setVisible(false);

								Ext.Ajax.request({
									method : 'POST',
									url : thisObj.config.AJAXPATH
											+ "?controller=user&action=updateGroup",
									success : function(response) {
										Ext.getCmp("w_u_l_g_w").close();
									},
									failure : function(response) {
										Ext.Msg.alert('failure',
												response.responseText);
										Ext.getCmp("w_u_l_g_w").close();
									},
									params : {
										 username : username
										,privileges : s.substring(0,s.length-1)
									}
								});
							}
						}]

					});

					var win = new Ext.Window({
								id : 'w_u_l_g_w',
								layout : 'fit',
								title:username+" "+il8n.User.Group,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
							});
					win.show(this);
				}
		    }, {
				text : il8n.Privilege,
				handler : function() {
					var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_l_p_t',
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

						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user&action=getPrivilege&username="
								+ username

					});

					var win = new Ext.Window({
								id : 'w_u_l_p_w',
								layout : 'fit',
								title:username+" "+il8n.Privilege,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
							});
					win.show(this);

				}
			}, {
				text : il8n.Subject,
				handler : function() {
					var username = Ext.getCmp(domid).getSelectionModel().selection.record.data.username;
					var tree = new Ext.tree.TreePanel({
						id:'w_u_l_s_t',
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

						dataUrl : thisObj.config.AJAXPATH
								+ "?controller=user&action=getSubject&username="
								+ username

					});

					var win = new Ext.Window({
								id : 'w_u_l_s_w',
								layout : 'fit',
								title:username+" "+il8n.Subject,
								width : 500,
								height : 300,
								modal  :true,
								items : [tree]
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


