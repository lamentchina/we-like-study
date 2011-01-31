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
	        bodyStyle:'padding:5px 5px 0',
	        width: 350,
	        defaults: {width: 100},
	        defaultType: 'textfield',	        
	
	        items: [{
	                fieldLabel: il8n.User.Name,
	                width:150,
	                vtype:"alphanum",
	                name: 'username',
	                allowBlank:false
	            },{
	                fieldLabel: il8n.User.PassWord,
	                width:150,
	                vtype:"alphanum",
	                name: 'password',
	                inputType:'password',
	                allowBlank:false
	            },{
	                fieldLabel: il8n.CheckCAPTCHA,
	                width:150,
	                vtype:"alphanum",
	                enableKeyEvents:true,
	                name: 'CAPTCHA',
	                allowBlank:false,
	                id:'CAPTCHA'
	            }, new Ext.BoxComponent({
	            	fieldLabel: il8n.CAPTCHA,
	                height: 32, // give north and south regions a height
	                autoEl: {
	                    tag: 'div',
	                    html:'<img style="width:100px; height:28px;" id="captcha" src="'+thisObj.config.libPath+'securimage/'+'securimage_show.php" alt="CAPTCHA Image" />'
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
			        	$('#captcha').attr("src",thisObj.config.libPath+'securimage/'+'securimage_show.php?wlstemp='+Math.random());
			        }
	        	},{
	        		text:'注册'
	        		,handler:function(){
	        			thisObj.register();
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
	register:function(){
		var thisObj = this;
		var form = new Ext.form.FormPanel({
			id:'wls_user_register_form',
			labelWidth: 75, 
	        frame:true,
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
	                name: 'CAPTCHA',
	                allowBlank:false,
	                id:'CAPTCHA_reg'
	            }, new Ext.BoxComponent({
	            	fieldLabel: il8n.CAPTCHA,
	                height: 32, 
	                autoEl: {
	                    tag: 'div',
	                    html:'<img style="width:100px; height:28px;" id="captcha_reg" src="'+thisObj.config.libPath+'securimage/'+'securimage_show.php" alt="CAPTCHA Image" />'
	                }
	            })
	        ],

	        buttons: [{
			    	 text: '注册'
			        ,handler:function(){
			        	var obj = form.getForm().getValues();
			    		Ext.Ajax.request({				
							method:'POST',				
							url:thisObj.config.AJAXPATH+"?controller=user&action=register",				
							success:function(response){				
								var obj = jQuery.parseJSON(response.responseText);
								if(obj.msg=='ok'){
									location.reload();
								}
								$.blockUI({
									message: '<h1>'+il8n.Error+'</h1>' 
								}); 					
								setTimeout($.unblockUI, 2000); 
								
								$('#captcha_reg').attr("src",thisObj.config.libPath+'securimage/'+'securimage_show.php?wlstemp='+Math.random());
							},				
							failure:function(response){	

							},				
							params:obj				
						});
			        }
	        	},{
			    	 text: il8n.ReCAPTCHA
			        ,handler:function(){
			        	$('#captcha_reg').attr("src",thisObj.config.libPath+'securimage/'+'securimage_show.php?wlstemp='+Math.random());
			        }
	        	}
	        ]
		});
		Ext.getCmp('CAPTCHA').on('keyup', function(obj,e) {
			if(e.getKey()=='13'){
				thisObj.login();
			}
		});
		
		var window_l = new Ext.Window({
			title:'注册',
	        width: 250,
	        height: 200,
	        layout: 'fit',
	        plain:true,
	        bodyStyle:'padding:5px;',
	        buttonAlign:'center',
	        items: [form],       
	        modal:true
	    });
		
		window_l.show();
	},	
	login:function(){

		var thisObj = this;
		var form = Ext.getCmp('wls_user_login_form').getForm();

    	if(form.isValid()){
			$.blockUI({
				message: '<h1>'+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
			});     		
    		var obj = form.getValues();
    		Ext.Ajax.request({				
				method:'POST',				
				url:thisObj.config.AJAXPATH+"?controller=user&action=login&temp="+Math.random(),				
				success:function(response){				
					var obj = jQuery.parseJSON(response.responseText);
					if(obj.msg=='ok'){
						location.reload();
					}
					$.blockUI({
						message: '<h1>'+obj.msg+'</h1>' 
					}); 					
					setTimeout($.unblockUI, 2000); 
					
					$('#captcha').attr("src",thisObj.config.libPath+'securimage/'+'securimage_show.php?wlstemp='+Math.random());
				},				
				failure:function(response){	
					$.unblockUI();
				    Ext.Msg.alert('failure',response.responseText);
				    $('#captcha').attr("src",thisObj.config.libPath+'securimage/'+'securimage_show.php?wlstemp='+Math.random());
				},				
				params:obj				
			});
    	}else{
    		 Ext.Msg.alert('输入错误','你没有输入所需要的信息!请输入<br/><br/><b>用户名</b><br/><b>密码</b><br/><b>验证码</b>');
    	}
	},
	logOut:function(){
		window.location.href=this.config.AJAXPATH+"?controller=user&action=logout";
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
								+ "?controller=user&action=getTreePrivilege&username="
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
								+ "?controller=user&action=getTreeSubject&username="
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
					
					win.show();
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
		store.load({params:{start:0, limit:15}});    
		return grid;
	}
	,getMyCenter:function(domid){		
		var border = new Ext.Panel({
			 id:domid
			,layout: 'border'
			,items:[
				new Ext.Panel({
					region:'east'
					,width:200						
					,html:'<div id="'+domid+'_subjects"></div>'
				})
				,new Ext.Panel({
					 region:'center'
					
					,html:'成绩曲线:<div id="chart1"></div><table width="100%" height="200px"><tr><td><div id="'+domid+'_uc"></div></td><td width="50%">知识点掌握:<br/>' +
							'<div id="flashcontent"><strong>You need to upgrade your Flash Player</strong></div>' +
							'</div></td></tr></table>'
				})
								
			]
		});
		return border;
	}
	,afterMyCenterAdded:function(domid){		
		var str = "<div><img style='border:2px;' src='"+this.config.filePath+'images/user/'+user_.myUser.photo+"' height='200' width='150' /></div>" +
				"<div>"+user_.myUser.money+"金币</div>" +
				"<div>参加科目数:"+user_.myUser.subject.split(',').length +"</div>" +
				"<div>权限操作数:"+user_.myUser.privilege.split(',').length+"</div>";				
		
	    $('#'+domid+'_uc').append(str);	    

	
		this.getMySubjectList(domid+'_subjects');					
		
		var so = new SWFObject(this.config.libPath+"am/amradar/amradar.swf", user_.myUser.id+"amradar", "320", "300", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath+"am/amradar/");
		so.addVariable("chart_id", user_.myUser.id+"amradar");
		so.addVariable("settings_file", encodeURIComponent(this.config.AJAXPATH+"?controller=knowledge_log&action=getMyRaderSetting"));
		so.write("flashcontent");
		
		var so = new SWFObject(this.config.libPath+"am/amline/amline.swf", user_.myUser.id+"amline", "100%", "200", "8", "#FFFFFF");
		so.addVariable("path", this.config.libPath+"am/amline/");
		so.addVariable("settings_file", encodeURIComponent(this.config.AJAXPATH+"?controller=subject&action=getMyQuizLine"));	
		so.write("chart1");		
	}

	,getMySubjectList:function(domid){
		var thisObj = this;
		var store = new Ext.data.JsonStore({
		    autoDestroy: true,
		    url: thisObj.config.AJAXPATH+'?controller=user&action=getSubject&username='+user_.myUser.username,
		    root: 'data',
		    idProperty: 'id',
		    fields: ['id','name','id_level', 'ids_level_knowledge']
		});
		store.load();
		
		var cm = new Ext.grid.ColumnModel({
		    defaults: {
		        sortable: true   
		    },
		    columns: [{
		             header: '名称'
		            ,dataIndex: 'name'
		            ,width:150
		        }, {
		             header: '编号 '
		            ,dataIndex: 'id_level'
		            ,hidden:true
		        }
		    ]
		});
		var grid = new Ext.grid.GridPanel({
		    store: store,
		    frame:true,
		    title: "我的科目",
		    cm: cm,        
		    renderTo: domid,
		   // width: '90%',
		    height: 500,
		    loadMask:true
		});
		grid.addListener('rowclick',function(t,r,e){			
			var id_k = t.store.data.items[r].data.ids_level_knowledge;
			var obj = document.getElementById(user_.myUser.id+"amradar");
			obj.reloadSettings(thisObj.config.AJAXPATH+"?controller=knowledge_log&action=getMyRaderSetting&id="+id_k);
			
			var id_s = t.store.data.items[r].data.id_level;
			var objs = document.getElementById(user_.myUser.id+"amline");
			objs.reloadSettings(thisObj.config.AJAXPATH+"?controller=subject&action=getMyQuizLine&id_level_subject_="+id_s);				
		}); 
	}
});