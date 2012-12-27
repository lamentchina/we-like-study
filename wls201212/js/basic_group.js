var basic_group = {
	
	version: "2012X1"
	
	,type: null
	,grid : null
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=basic_group&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				basic_group.config = response;
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
	
	,initGrid : function(){
		var config = {
			columns: [
				{ display: top.il8n.title, name: 'name', align: 'left', width: 140, minWidth: 60, render: function(a,b){
					var str = a.name;
					for(var i=0; i<a.code.length-2; i++){
						str = "-"+str;
					}
					return str;
				} },
				{ display: top.il8n.type, name: 'type', isSort: false, render: function(a,b){
					for(var i=0; i<basic_group.config.type.length; i++){
						if(basic_group.config.type[i].code == a.type){
							return basic_group.config.type[i].value;
						}
					}
				} },
				{ display: top.il8n.basic_group.code, name: 'code', width: 50 ,isSort : false},
				{ display: top.il8n.basic_group.status, name: 'status', width: 55, isSort : false , render: function(a,b){
					if(a.status=="0"){
						return top.il8n.disabled;
					}else if(a.status=="1"){
						return top.il8n.enabled;
					}else{
						return a.status;
					}
				} },
				{ display: top.il8n.basic_group.count_users, name: 'count_users'}
			], rownumbers:true,height:'100%',usePager:false,
			parms : {username : top.basic_user.username,
				session : MD5( top.basic_user.session +((new Date()).getHours()))},
			url : myAppServer()+"&class=basic_group&function=getGrid",
			method  : "POST",
			id : "basic_group__grid",
			toolbar: { items: [] }
		};
		
		var permission = top.basic_user.permission;
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='12'){
				permission = permission[i].children;				
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1201'){
				permission = permission[i].children;				
			}
		}		
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='120111'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_group.upload();
					}
				});
			}else if(permission[i].code=='120112'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_group.download();
					}
				});
			}else if(permission[i].code=='120122'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_group.delet();
					}
				});
			}else if(permission[i].permission=='120123'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected;
						if(basic_group.grid.options.checkbox){
							selected = basic_group.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_group.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_group__update.html?id='+selected.id+'&random='+Math.random(), height: 450,width: 450
							,title: top.il8n.modify
							,isHidden: false
						});
					}
				});
			}else if(permission[i].code=='120121'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						top.$.ligerDialog.open({ 
							url: 'basic_group__insert.html?random='+Math.random(), height: 500,width: 400
							,title: top.il8n.add
							,isHidden: false
						});
					}
				});
			}else if(permission[i].code=='120190'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected;
						if(basic_group.grid.options.checkbox){
							selected = basic_group.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_group.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_group_2_permission__tree.html?id='+selected.id+'&random='+Math.random(), height: 500,width: 400
							,title: top.il8n.modify
							,isHidden: false
						});
					}
				});
			}
		}
		
		basic_group.grid = $(document.body).ligerGrid(config);
	}

	,insert: function(
			dom, //如果为空,则返回一个 ligerForm 的参数配置对象
			afterInsert //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		
		var config = {
			id: 'basic_group__insert',
			fields: [
				 { display: top.il8n.title, name: "basic_group__name",  type: "text",  validate: { required:true, minlength:3, maxlength:10} }
				,{ display: top.il8n.type, name: "basic_group__type", type: "select" , options :{data : top.il8n.basic_group__types, valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.code, name: "basic_group__code",  type: "text", validate: {required:true, digits:true, minlength:2, maxlength:10 } }
				,{ display: top.il8n.icon, name: "setIcon", type: "text" }
				,{ display: top.il8n.status, name: "basic_group__status", type: "select" , options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.remark, name: "basic_group__remark",  type: "text" }
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			var form = $(dom).ligerForm(config);
			$.ligerui.get("setIcon").setDisabled();
			if($.browser.msie && top != self){
				$.ligerui.get('basic_group__type').setData(top.il8n.basic_group__types);
			}			
			
			$(dom).append('<br/><br/><br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="basic_group__submit" class="l-button l-button-submit" /></div>' );
			
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
					if(basic_user.ajaxState)return;
					basic_user.ajaxState = true;
					$("#basic_group__submit").attr("value",top.il8n.waitting);
					
					basic_user.type = $.ligerui.get('basic_group__type').getValue();
					$.ajax({
						url: myAppServer() + "&class=basic_group&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 name: $.ligerui.get('basic_group__name').getValue()
								,type: $.ligerui.get('basic_group__type').getValue()
								,code: $.ligerui.get('basic_group__code').getValue()
								,icon: $.ligerui.get('setIcon').getValue()
								,status: $.ligerui.get('basic_group__status').getValue()
								,remark: $.ligerui.get('basic_group__remark').getValue()
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
								basic_group.ajaxState = false;
								basic_group.type = $.ligerui.get('basic_group__type').getValue();
								$("#basic_group__submit").val(top.il8n.submit);	
								
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								basic_group.ajaxState = false;
								$("#basic_group__submit").val(top.il8n.submit);									
								alert(response.msg);
							}
						},
						error : function(){
							alert(top.il8n.disConnect);
						}
					});	
				}
			});
			
			return form;
		}
	}
	
	,afterFormCreated: function(){
		var dom = $("li",$("#setIcon").parent().parent().parent())[2];
		var str = 	"$.ligerDialog.open({"
			+"url: 'basic_parameter__icons16x16.html', height: 300,width: 400"
			+",title: top.il8n.icon"
			+",isHidden: false"
		+"});	";
		$(dom).append('&nbsp;<a onclick="'+str+'" href="#" title="'+top.il8n.icon+'"><img id="setIcon_img" src="../file/icon16x16/icon-16-redirect.png"></a>');
	}	
	
	,ajaxState: false 	

	,afterInsert: function(){
		$('#buttons').empty();
		
		//组织机构类型的编码,可以额外补充组织机构信息
		if(this.type=='2'){
			$('#buttons').append('<input type="submit" style="width:100px;" value="'+top.il8n.basic_group.addDepartmentInfo+'" onclick="basic_group.addExtend()" class="l-button l-button-submit" />');
		}
	}
	
	//如果在添加用户组的时候,选择的类型是 组织机构 ,就意味着同时也添加了一条 组织机构 记录
	//还需要再补充组织机构的信息
	,departmentId: null
	,addExtend: function(){
		top.$.ligerDialog.open({ 
			url: 'basic_department__update.html?id='+basic_user.insertResponse.person+'&random='+Math.random(), height: 540,width: 700
			,title: top.il8n.basic_user.addPersonInfo
			,isHidden: false
		});
	}
	
	,ajaxData: null
	,update: function(dom,afterAjax){
		var config = this.insert();
		var form = $(dom).ligerForm(config);
		
		$(dom).append('<br/><br/><br/><br/><input type="submit" value="'+top.il8n.modify+'" id="basic_group__submit" class="l-button l-button-submit" />' );
		
		
		//从服务端读取信息,填充表单内容
		$.ajax({
			url: myAppServer() + "&class=basic_group&function=view"
			,data: {
				code: getParameter("code", window.location.toString() )
				
				//服务端权限验证所需
				,personname: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    basic_group.ajaxData = response;
			    $.ligerui.get('basic_group__name').setValue(response.name);
				 $.ligerui.get('basic_group__type').setValue(response.type);
				 $.ligerui.get('basic_group__code').setValue(response.code);
				 $.ligerui.get('setIcon').setValue(response.icon);
				 $.ligerui.get('basic_group__status').setValue(response.status);
				 $.ligerui.get('basic_group__remark').setValue(response.remark);
				 
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
				if(basic_group.ajaxState)return;
				basic_group.ajaxState = true;
				$("#basic_group__submit").attr("value",top.il8n.waitting);
				
				//将被传递到后台,执行修改操作的数据
				var data = {
					//肯定包含 主键 id
				    code: getParameter("code", window.location.toString() )
				};
				//检查表单中,有没有被更改的内容.只将有更改的内容传递到服务端
				//其中,密码比较特殊

				if(basic_group.ajaxData.name != $.ligerui.get('basic_group__name').getValue() )
					data.name = $.ligerui.get('basic_group__name').getValue();
				if(basic_group.ajaxData.type != $.ligerui.get('basic_group__type').getValue() )
					data.type = $.ligerui.get('basic_group__type').getValue();
				if(basic_group.ajaxData.code != $.ligerui.get('basic_group__code').getValue() )
					data.code = $.ligerui.get('basic_group__code').getValue();
				if(basic_group.ajaxData.icon != $.ligerui.get('setIcon').getValue() )
					data.icon = $.ligerui.get('setIcon').getValue();
				if(basic_group.ajaxData.status != $.ligerui.get('basic_group__status').getValue() )
					data.status = $.ligerui.get('basic_group__status').getValue();
				if(basic_group.ajaxData.remark != $.ligerui.get('basic_group__remark').getValue() )
					data.remark = $.ligerui.get('basic_group__remark').getValue();
				
				//如果一个都没有被修改,就不会传到服务端
				var toUpdateColumn = 0;
				for (var obj in data){
					toUpdateColumn ++;
				}
				if(toUpdateColumn==1){
					alert(top.il8n.nothingModified);
					return;
				}
				$.ajax({
					url: myAppServer() + "&class=basic_group&function=update"
					,data: {
						json: $.ligerui.toJSON(data)
						
						//服务端权限验证所需
						,personname: top.basic_user.username
						,session: MD5( top.basic_user.session +((new Date()).getHours()))
					}
					,type: "POST"
					,dataType: 'json'						
					,success: function(response) {		
						//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
						if(response.state==1){
							basic_group.ajaxState = false;
							$("#basic_group__submit").val(top.il8n.submit);	
							alert(top.il8n.done);
							//如果参数中,有 回调函数,则执行
							if ( typeof(afterAjax) == "string" ){
								eval(afterAjax);
							}
						
						//服务端添加失败
						}else if(response.state==0){
							basic_group.ajaxState = false;
							$("#basic_group__submit").val(top.il8n.submit);									
							alert(response.msg);
						}
					}
					,error : function(){
						alert(top.il8n.disConnect);
					}
				});
			}
		});
		
	}		
	
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = basic_group.grid.getSelecteds();
		//如果一行都没有选中,就报错并退出函数
		if(selected.length==0){alert(il8n.noSelect);return;}
		//弹框让用户最后确认一下,是否真的需要删除.一旦删除,数据将不可恢复
		var ids = "";
		//遍历每一行元素,获得 id 
		for(var i=0; i<selected.length; i++){
			ids += selected[i].code+","
		}
		ids = ids.substring(0,ids.length-1);		
		if(confirm(il8n.sureToDelete)){				
			
			$.ajax({
				url: myAppServer() + "&class=basic_group&function=delete",
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
						basic_group.grid.loadData();
					}
				},
				error : function(){
					//网络通信失败,则删除按钮再也不能点了
					alert(top.il8n.disConnect);
				}
			});				
		}	
	}
	
	,upload : function(){
		var dialog;
		if($.ligerui.get("basic_group__grid_upload_d")){
			dialog = $.ligerui.get("basic_group__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='basic_group__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('basic_group__grid_file'),
				action: '../php/myApp.php?class=basic_group&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/basic_group__add.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					basic_group.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "basic_group__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_group__grid_file"),
				modal : true
			});
		}
	}
	
	,download: function(){
		var dialog;
		if($.ligerui.get("basic_group__grid_download_d")){
			dialog = $.ligerui.get("basic_group__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='basic_group__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "basic_group__grid_download_d",
				width : 350,
				height : 200,
				target : $("#basic_group__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=basic_group&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					$.ligerDialog.error(response.msg);
				}else if(response.state==1){
					$("#basic_group__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a>");
				}
			},
			error : function(){
				manager.close();
				$.ligerDialog.error(top.il8n.AJAX.disConnect);
			}
		});		
	}
};