var basic_workflow = {

	id : 0 
	,session : ''
	,action : ''
	,actiontime : ''
	,username : 'admin'
	,realname : ""
	,group : ""
	,groupname : ""
	,money : 0
	,credits : 0
	,permission : {}
	,lastlogintime : "1900-01-01"
	,ip : "127.0.0.1"
	,photo : ""
	,time_created : "1900-01-01"
	,type: ""
		
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=basic_workflow&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				basic_workflow.config = response;
				if ( typeof(afterAjax) == "string" ){
					eval(afterAjax);
				}else if( typeof(afterAjax) == "function"){
					afterAjax();
				}
			},
			error : function(){				
				alert(top.il8n.disConnect);
			}
		});	
	}	
	
	//页面列表ligerUI控件
	,grid : null
	
	,searchOptions: {}
	
	/**
	 * 初始化页面列表
	 * 需要依赖一个空的 document.body 
	 * */
	,initGrid : function(){
		var config = {
				id: 'basic_workflow__grid'
				,height:'100%'
				,columns: [
					{ display: top.il8n.basic_workflow.username, name: 'username' },
					{ display: top.il8n.money, name: 'money' },
					{ display: top.il8n.basic_workflow.money2, name: 'money2', hide: true },
					{ display: top.il8n.basic_workflow.money3, name: 'money3', hide: true },
					{ display: top.il8n.type, name: 'type', isSort: false, render: function(a,b){
						
						for(var i=0; i<basic_workflow.config.type.length; i++){
							if( basic_workflow.config.type[i].code == a.type){
								return basic_workflow.config.type[i].value;
							}
						}				
					} },
					{ display: top.il8n.status, name: 'status', width: 55 , render: function(a,b){
						for(var i=0; i<basic_workflow.config.status.length; i++){
							if(basic_workflow.config.status[i].code == a.status){
								return basic_workflow.config.status[i].value;
							}
						}
					} },
					{ display: top.il8n.basic_workflow.groups, name: 'groups', width: 55, isSort : false },
					
					
					{ display: top.il8n.basic_workflow.creater, name: 'creater', width: 55, isSort : false },
					{ display: top.il8n.time_created, name: 'time_created', width: 55 }
				],  pageSize:20 ,rownumbers:true,
				parms : {
					username: top.basic_user.username
					,session: MD5( top.basic_user.session +((new Date()).getHours()))
					,search: $.ligerui.toJSON( basic_workflow.searchOptions )
				},
				url: myAppServer() + "&class=basic_workflow&function=getGrid",
				method: "POST",				
				toolbar: { items: []}
		};
		
		//配置列表表头的按钮,根据当前用户的权限来初始化
		var permission = [];
		for(var i=0;i<top.basic_workflow.permission.length;i++){
			if(top.basic_workflow.permission[i].code=='12'){
				permission = top.basic_workflow.permission[i].children;
				for(var j=0;j<permission.length;j++){
					if(permission[j].code=='1202'){
						permission = permission[j].children;
					}
				}				
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='120201'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_workflow.search();
					}
				});
			}else if(permission[i].code=='120211'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_workflow.upload();
					}
				});
			}else if(permission[i].code=='120212'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_workflow.download();
					}
				});
			}else if(permission[i].code=='120222'){
				//若可以执行删除操作,则必定是多选批量删除,则列表右侧应该提供多选勾选框
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_workflow.delet();
					}
				});
			}else if(permission[i].code=='120223'){
				//拥有 修改一个用户 的权限
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//basic_workflow.update();
						var selected;
						if(basic_workflow.grid.options.checkbox){
							selected = basic_workflow.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_workflow.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_workflow__update.html?id='+selected.id+'&random='+Math.random(), height: 500,width: 400
							,title: top.il8n.modify
							,isHidden: false
						});						
					}
				});
			}else if(permission[i].code=='120221'){
				//拥有 添加一个用户 的权限
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//basic_workflow.insert();
						top.$.ligerDialog.open({ 
							url: 'basic_workflow__insert.html?random='+Math.random(), height: 500,width: 400
							,title: top.il8n.add
							,isHidden: false
						});
					}
				});
			}else if(permission[i].code=='120202'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_workflow.view();
					}
				});
			}else if(permission[i].code=='120290'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected;
						if(basic_workflow.grid.options.checkbox){
							selected = basic_workflow.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_workflow.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_group_2_user__tree.html?username='+selected.username+'&random='+Math.random(), height: 500,width: 400
							,title: top.il8n.basic_workflow.updateUserGroup
							,isHidden: false
						});	
					}
				});
			}
		}
		
		basic_workflow.grid = $(document.body).ligerGrid(config);
	}
	
	
	
	
	
	/**
	 * 删除一个或多个用户
	 * 如果用户拥有 删除权限 
	 * 则前端列表必定是一个带 checkBox 的
	 * */
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = basic_workflow.grid.getSelecteds();
		//如果一行都没有选中,就报错并退出函数
		if(selected.length==0){alert(il8n.noSelect);return;}
		//弹框让用户最后确认一下,是否真的需要删除.一旦删除,数据将不可恢复
		if(confirm(il8n.sureToDelete)){
			var ids = "";
			//遍历每一行元素,获得 id 
			for(var i=0; i<selected.length; i++){
				ids += selected[i].id+","
			}
			ids = ids.substring(0,ids.length-1);				
			
			$.ajax({
				url: myAppServer() + "&class=basic_workflow&function=delete",
				data: {
					ids: ids 
					
					//服务端权限验证所需
					,username: top.basic_user.username
					,session: MD5( top.basic_user.session +((new Date()).getHours()))
				},
				type: "POST",
				dataType: 'json',
				success: function(response) {
					if(response.state==1){
						basic_workflow.grid.loadData();
					}
				},
				error : function(){
					//网络通信失败,则删除按钮再也不能点了
					alert(top.il8n.disConnect);
				}
			});				
		}		
	}
	
	/**
	 * 添加一个用户
	 * 前端以表单的形式向后台提交数据,服务端AJAX解析入库,
	 * 服务端还会反馈一些数据,比如 用户编号 等
	 * */
	,insert: function(
			dom, //如果为空,则返回一个 ligerForm 的参数配置对象
			afterAjax //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		
		var config = {
			id: 'basic_workflow__addForm',
			fields: [
				{ display: top.il8n.basic_workflow.username, name: "basic_workflow__username",  type: "text",  validate: { required:true, minlength:3, maxlength:10} }
				,{ display: top.il8n.basic_workflow.password, name: "basic_workflow__password",  type: "password", validate: { required:true } }
				,{ display: top.il8n.type, name: "basic_workflow__type", type: "select" , options :{data : top.il8n.basic_workflow__types, valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.money, name: "basic_workflow__money",  type: "text" , validate: {required: true, digits: true}}
				,{ display: top.il8n.basic_workflow.money2, name: "basic_workflow__money2",  type: "text" , validate: {digits: true}}
				,{ display: top.il8n.basic_workflow.money3, name: "basic_workflow__money3",  type: "text" , validate: {digits: true}}
				,{ display: top.il8n.status, name: "basic_workflow__status", type: "select" , options :{data : top.il8n.basic_workflow__status, valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.remark, name: "basic_workflow__remark",  type: "text" }
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			$(dom).ligerForm(config);
			
			//ligerUI 浏览器不兼容 BUG 处理
			if($.browser.msie && top != self){
				$.ligerui.get('basic_workflow__type').setData(top.il8n.basic_workflow__types);
				$.ligerui.get('basic_workflow__status').setData(top.il8n.basic_workflow__status);
			}
			
			$(dom).append('<br/><br/><br/><br/><table style="width:80%"><tr><td style="width:25%"><input type="submit" value="'+top.il8n.submit+'" id="basic_workflow__submit" class="l-button l-button-submit" /></td></tr></table>' );
			
			var v = $(dom).validate({
				debug: true,
				//JS前端验证错误
				errorPlacement: function (lable, element) {
					if (element.hasClass("l-textarea")) {
					element.addClass("l-textarea-invalid");
					}
					else if (element.hasClass("l-text-field")) {
					element.parent().addClass("l-text-invalid");
					} 
				},
				//JS前端验证通过
				success: function (lable) {
					var element = $("[ligeruiid="+$(lable).attr('for')+"]",$("form"));
					if (element.hasClass("l-textarea")) {
						element.removeClass("l-textarea-invalid");
					} else if (element.hasClass("l-text-field")) {
						element.parent().removeClass("l-text-invalid");
					}
				},
				//提交表单,在表单内 submit 元素提交之后,要与后台通信
				submitHandler: function () {
					if(basic_workflow.ajaxState)return;
					basic_workflow.ajaxState = true;
					$("#basic_workflow__submit").attr("value",top.il8n.waitting);
					
					basic_workflow.type = $.ligerui.get('basic_workflow__type').getValue();
					$.ajax({
						url: myAppServer() + "&class=basic_workflow&function=insert",
						data: {
							json:$.ligerui.toJSON({
								username: $.ligerui.get('basic_workflow__username').getValue()
								,password: $.ligerui.get('basic_workflow__password').getValue()
		
								,type: $.ligerui.get('basic_workflow__type').getValue()
								
								,money: $.ligerui.get('basic_workflow__money').getValue()
								,money2: $.ligerui.get('basic_workflow__money2').getValue()
								,money3: $.ligerui.get('basic_workflow__money3').getValue()
								,remark: $.ligerui.get('basic_workflow__remark').getValue()
							}),
							
							//服务端权限验证所需
							username: top.basic_user.username,
							session: MD5( top.basic_user.session +((new Date()).getHours()))
						},
						type: "POST",
						dataType: 'json',						
						success: function(response) {		
							//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
							if(response.state==1){
								basic_workflow.ajaxState = false;
								$("#basic_workflow__submit").val(top.il8n.submit);	
								basic_workflow.insertResponse = {
								    user: response.data.id_user
								    ,person: response.data.id_person
								    ,extend: response.data.id_extend
								};
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterAjax) == "string" ){
									eval(afterAjax);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								basic_workflow.ajaxState = false;
								$("#basic_workflow__submit").val(top.il8n.submit);									
								alert(response.msg);
							}
						},
						error : function(){
							alert(top.il8n.disConnect);
						}
					});	
				}
			});
		}
	}
	
	//如果添加成功,服务端会返回一些添加成功后的编号数据
	,insertResponse: {
		 user: null //用户添加成功后,用户编号
		,person: null //用户信息对用的 人员详细信息,人员信息编号
		,extend: null //如果这个用户类型选的不是 系统 类型,则会返回一个 用户类型扩展信息表 的编号
	}
	
	//AJAX 通信状态,如果为TRUE,则表示服务端还在通信中	
	,ajaxState: false 
	
	,update: function(dom,afterAjax){
		var config = this.insert();
		$(dom).ligerForm(config);
		//ligerUI 浏览器不兼容 BUG 处理
		if($.browser.msie && top != self){
			$.ligerui.get('basic_workflow__type').setData(top.il8n.basic_workflow__types);
			$.ligerui.get('basic_workflow__status').setData(top.il8n.basic_workflow__status);
		}
		
		$(dom).append('<br/><br/><br/><br/><input type="submit" value="'+top.il8n.modify+'" id="basic_workflow__submit" class="l-button l-button-submit" />' );
		
		//从服务端读取信息,填充表单内容
		$.ajax({
			url: myAppServer() + "&class=basic_workflow&function=view"
			,data: {
				id: getParameter("id", window.location.toString() )
				
				//服务端权限验证所需
				,username: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    basic_workflow.ajaxData = response;
				$.ligerui.get('basic_workflow__username').setValue(response.username);
				
				$.ligerui.get('basic_workflow__password').setValue(response.password);

				$.ligerui.get('basic_workflow__type').setValue(response.type);
				
				$.ligerui.get('basic_workflow__status').setValue(response.status);
				
				$.ligerui.get('basic_workflow__money').setValue(response.money);
				$.ligerui.get('basic_workflow__money2').setValue(response.money2);
				$.ligerui.get('basic_workflow__money3').setValue(response.money3);
				$.ligerui.get('basic_workflow__remark').setValue(response.remark);				
				
				$.ligerui.get('basic_workflow__type').setDisabled();
				$.ligerui.get('basic_workflow__username').setDisabled();
			}
		});
			
		var v = $(dom).validate({
			debug: true,
			//JS前端验证错误
			errorPlacement: function (lable, element) {
				if (element.hasClass("l-textarea")) {
				element.addClass("l-textarea-invalid");
				}
				else if (element.hasClass("l-text-field")) {
				element.parent().addClass("l-text-invalid");
				} 
			},
			//JS前端验证通过
			success: function (lable) {
				var element = $("[ligeruiid="+$(lable).attr('for')+"]",$("form"));
				if (element.hasClass("l-textarea")) {
					element.removeClass("l-textarea-invalid");
				} else if (element.hasClass("l-text-field")) {
					element.parent().removeClass("l-text-invalid");
				}
			},
			//提交表单,在表单内 submit 元素提交之后,要与后台通信
			submitHandler: function () {
				alert(1);
				
			}
		});
	}	
	
	/**
	 * 与表格功能对应的 查询条件 
	 * 
	 * 查询条件有 用户名关键字,状态,类型,金币,用户组关键字
	 * */
	,search: function(){
		var formD;
		if($.ligerui.get("formD")){
			formD = $.ligerui.get("formD");
			formD.show();
		}else{
			var form = $("<form id='form'></form>");
			$(form).ligerForm({
				inputWidth: 170
				,labelWidth: 90
				,space: 40
				,fields: [
					 { display: top.il8n.basic_workflow.username, name: "basic_workflow__search_username", newline: false, type: "text" }
					,{ display: top.il8n.type, name: "basic_workflow__search_type", newline: true, type: "select", options :{data : top.il8n.basic_workflow__types, valueField : "code" , textField: "value" } }
					,{ display: top.il8n.status, name: "basic_workflow__search_status", newline: true, type: "select", options :{data : top.il8n.basic_workflow__status, valueField : "code" , textField: "value" } }
					,{ display: top.il8n.money, name: "basic_workflow__search_money", newline: true, type: "number" }
					,{ display: top.il8n.basic_workflow.groups, name: "basic_workflow__search_groups", newline: true, type: "text" }
				]
			}); 
			$.ligerDialog.open({
				id : "formD",
				width : 350,
				height : 200,
				content : form,
				title : top.il8n.search,
				buttons : [
				    //清空查询条件
					{text:top.il8n.clear,onclick:function(){
						$.ligerui.get("basic_workflow__grid").options.parms.search = "{}";
						$.ligerui.get("basic_workflow__grid").loadData();
						
						$.ligerui.get("basic_workflow__search_username").setValue('');
						$.ligerui.get("basic_workflow__search_type").setValue('');
						$.ligerui.get("basic_workflow__search_status").setValue('');
						$.ligerui.get("basic_workflow__search_money").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
						var data = {};
						var username = $.ligerui.get("basic_workflow__search_username").getValue();
						var type = $.ligerui.get("basic_workflow__search_type").getValue();
						var status = $.ligerui.get("basic_workflow__search_status").getValue();
						var money = $.ligerui.get("basic_workflow__search_money").getValue();
						var groups = $.ligerui.get("basic_workflow__search_groups").getValue();
						
						if(username!="")data.username = username;
						if(type!="")data.type = type;
						if(status!="")data.status = status;
						if(groups!="")data.groups = groups;
						if(money!=0)data.money = money;
						
						$.ligerui.get("basic_workflow__grid").options.parms = {
	
							username: top.basic_user.username
							,session: MD5( top.basic_user.session +((new Date()).getHours()))
							,search: $.ligerui.toJSON(data)
							
						};
						$.ligerui.get("basic_workflow__grid").loadData();
				}}]
			});
		}
	}
	
	,check: function(){
		$.ajax({
			url: myAppServer() + "&class=basic_workflow&function=check"
			,data: {				
				 username: basic_user.username
				,session: MD5( basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    if(response.state==1){
			    	basic_user.session = response.session;
			    	//每隔15分钟后台更新一下
			    	setTimeout( basic_workflow.check,1000*60*15 );
			    }
			}
		});
	}
};