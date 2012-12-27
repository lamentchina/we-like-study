var basic_department = {
	
	version: "2012X1"
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=basic_department&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				basic_department.config = response;
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
		
	,type: null
	,grid: null
	
	,initGrid : function(){
		var config = {
			columns: [
				{ display: top.il8n.title, name: 'name', align: 'left', width: 140,render: function(a,b){
					var str = a.name;
					for(var i=0; i<a.code.length-2; i++){
						str = "-"+str;
					}
					return str;
				} }
				,{ display: top.il8n.type, name: 'type', isSort: false,width: 100, render: function(a,b){
					for(var i=0; i< basic_department.config.type.length ; i++){
						if(basic_department.config.type[i].code == a.type){
							return basic_department.config.type[i].value;
						}
					}
				} }
				,{ display: top.il8n.code, align: 'left', name: 'code' ,width: 50 ,isSort : false}
				,{ display: top.il8n.basic_department.count_users, name: 'count_users'}
			]
			,rownumbers:true
			,height:'100%'
			,usePager:false
			,parms : {
				username : top.basic_user.username,
				session : MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,url : myAppServer() + "&class=basic_department&function=getGrid"
			,method  : "POST"
			,id : "basic_department__grid"
			,toolbar: { items: [] }
		};
		
		var permission = top.basic_user.permission;
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='13'){
				permission = permission[i].children;
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1302'){
				permission = permission[i].children;
			}
		}		
		
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='130201'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_department.search();
					}
				});
			}else if(permission[i].code=='130202'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//basic_department.view();
						var selected;
						if(basic_department.grid.options.checkbox){
							selected = basic_department.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_department.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_department__view.html?id='+selected.id+'&random='+Math.random(), height: 550,width: 700
							,title: top.il8n.details
							,isHidden: false
						});							
					}
				});
			}else if(permission[i].code=='130211'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_department.upload();
					}
				});
			}else if(permission[i].code=='130212'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						basic_department.download();	
					}
				});
			}else if(permission[i].code=='130221'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						//basic_department.insert();	
						top.$.ligerDialog.open({ 
							url: 'basic_department__insert.html?random='+Math.random(), height: 550,width: 700
							,title: top.il8n.add
						});
					}
				});
			}else if(permission[i].code=='130222'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						basic_department.delet();							
					}
				});
			}else if(permission[i].code=='130223'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						//basic_department.update();
						var selected;
						if(basic_department.grid.options.checkbox){
							selected = basic_department.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_department.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_department__update.html?id='+selected.id+'&random='+Math.random(), height: 550,width: 700
							,title: top.il8n.modify
							,isHidden: false
						});							
					}
				});
			}else if(permission[i].code=='130290'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						basic_department.paper();	
					}
				});
			}
		}
		
		basic_department.grid = $(document.body).ligerGrid(config);
	}

	,insert: function(
			dom, //如果为空,则返回一个 ligerForm 的参数配置对象
			afterInsert //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		
		var config = {
			id: 'basic_department__insert',
			fields: [
				 { display: top.il8n.code, name: "basic_department__code", type: "text", validate: { required:true, minlength:3, maxlength:10} }
				,{ display: top.il8n.name, name: "basic_department__name", type: "text", validate: { required:true, minlength:3, maxlength:10}, newline: false }
				,{ display: top.il8n.type, name: "basic_department__type", type: "select" , options :{data : basic_department.config.type, valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.basic_department.address, name: "basic_department__address", type: "text", newline: false }
				,{ display: top.il8n.basic_department.phone, name: "basic_department__phone", type: "text" }
				,{ display: top.il8n.basic_department.fax, name: "basic_department__fax", type: "text", newline: false }				
				,{ display: top.il8n.basic_department.photo, name: "basic_department__photo", type: "text" }
				,{ display: top.il8n.basic_department.manager_name, name: "basic_department__manager_name", type: "text", newline: false }
				,{ name: "basic_department__manager_id", type: "hidden" }
				
				,{ display: top.il8n.basic_department.functions, name: "basic_department__functions", type: "text" , width:470 }				
				
				,{ display: top.il8n.status, name: "basic_department__status", type: "select" , options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.remark, name: "basic_department__remark", type: "text", newline: false }
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			var form = $(dom).ligerForm(config);
			
			$(dom).append('<br/><br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="basic_department__submit" class="l-button l-button-submit" /></div>' );
			
			var dom = $("#basic_department__manager_name").parent().parent().next();
			$.ligerui.get('basic_department__manager_name').setDisabled();
			$(dom).append('<a onclick="basic_department.setManager()" href="#" title="'+top.il8n.basic_department.setManager+'"><img src="../file/icon16x16/detail.gif"></a>');
			var dom = $("#basic_department__photo").parent().parent().next();
			$.ligerui.get('basic_department__photo').setDisabled();
			$(dom).append('<a onclick="basic_department.photo(\'basic_department__photo\')" href="#" title="'+top.il8n.basic_department.photo+'"><img src="../file/icon16x16/detail.gif"></a>');			
			
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
					$("#basic_department__submit").attr("value",top.il8n.waitting);
					
					basic_user.type = $.ligerui.get('basic_department__type').getValue();
					$.ajax({
						url: myAppServer() + "&class=basic_department&function=insert",
						data: {
							json:$.ligerui.toJSON({

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
								basic_department.ajaxState = false;
								basic_department.type = $.ligerui.get('basic_department__type').getValue();
								$("#basic_department__submit").val(top.il8n.submit);	
								
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								basic_department.ajaxState = false;
								$("#basic_department__submit").val(top.il8n.submit);									
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
	
	,setManager: function(){
		
	}
	
	,photo: function(id){
		$.ligerDialog.open({ 
			url: 'basic_parameter__photoUpload.html?id='+id+"&path="+$.ligerui.get(id).getValue(), height: 350,width: 400
			,isHidden: false
		});
	}	
	
	,ajaxState: false 	
	
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = basic_department.grid.getSelecteds();
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
				url: myAppServer() + "&class=basic_department&function=delete",
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
						basic_department.grid.loadData();
					}
				},
				error : function(){
					//网络通信失败,则删除按钮再也不能点了
					alert(top.il8n.disConnect);
				}
			});				
		}	
	}
	
	,update: function(){
		
	},
	upload : function(){
		var dialog;
		if($.ligerui.get("basic_department__grid_upload_d")){
			dialog = $.ligerui.get("basic_department__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='basic_department__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('basic_department__grid_file'),
				action: '../php/myApp.php?class=basic_department&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/basic_department__add.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					basic_department.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "basic_department__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_department__grid_file"),
				modal : true
			});
		}
	},
	download: function(){
		var dialog;
		if($.ligerui.get("basic_department__grid_download_d")){
			dialog = $.ligerui.get("basic_department__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='basic_department__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "basic_department__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_department__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=basic_department&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					$.ligerDialog.error(response.msg);
				}else if(response.state==1){
					$("#basic_department__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a>");
				}
			},
			error : function(){
				manager.close();
				$.ligerDialog.error(top.il8n.AJAX.disConnect);
			}
		});		
	}
};