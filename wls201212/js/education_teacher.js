/**
 * 分三个用户组看到教师的信息
 * 管理员
 *   看到所有的教师信息
 *   可以 增删改查数,权限按钮有:
 *     新增 修改 查询 删除 导入 导出 查看
 * 教师
 *   看到本部门的教师信息
 *   可以执行的操作有:
 *     查询 查看
 * 学生
 *   看到与我的学习科目有关的教师信息
 *   可以执行的操作有
 *     查询 查看
 *     
 * 在教师详细(查看)页面,也有部分按钮,按钮有:
 *   涉及的学生列表 涉及的科目列表
 * 
 * @version 201209
 * @author wei1224hf@gmail.com
 * */
var education_teacher = {

	version: "2012X1"
		
	,id : 0 
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=education_teacher&function=loadConfig",
			dataType: 'json'
	        ,type: "POST"
	        ,data: {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
	        } 
			,success : function(response) {
				education_teacher.config = response;
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
	
	/**
	 * 初始化页面列表
	 * 需要依赖一个空的 document.body 
	 * */
	,grid: function(){
		var gridColmuns = [
			[//管理员列
			   { display: top.il8n.id, name: 'id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_teacher.code, name: 'code' }
			]
			,[//学生列	
			   { display: top.il8n.id, name: 'id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_teacher.code, name: 'code' }			  
			  ]
			,[//教师列
			   { display: top.il8n.id, name: 'id', isSort: false, hide:true }
			   ,{ display: top.il8n.education_teacher.code, name: 'code' }	
			  ]
		];
		var config = {
			id: 'education_teacher__grid'
			,height:'100%'
			,columns: []
			,pageSize:20 
			,rownumbers:true
			,parms: {
                 username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
				,search: $.ligerui.toJSON( education_teacher.searchOptions )
			}
			,url: myAppServer() + "&class=education_teacher&function=grid"
			,method: "POST"				
			,toolbar: { items: []}
		};
		config.columns = gridColmuns[(top.basic_user.loginData.type)*1-1];  			
		
		//配置列表表头的按钮,根据当前用户的权限来初始化
		var permission = [];
		for(var i=0;i<top.basic_user.permission.length;i++){
			if(top.basic_user.permission[i].code=='18'){
				permission = top.basic_user.permission[i].children;
								
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1801'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_teacher.search();
					}
				});
			}else if(permission[i].code=='1811'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_teacher.upload();
					}
				});
			}else if(permission[i].code=='120203'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_teacher.download();
					}
				});
			}else if(permission[i].code=='1822'){
				//若可以执行删除操作,则必定是多选批量删除,则列表右侧应该提供多选勾选框
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_teacher.delet();
					}
				});
			}else if(permission[i].code=='1823'){
				//拥有 修改一个用户 的权限
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//education_teacher.update();
						var selected;
						if(education_teacher.grid.options.checkbox){
							selected = education_teacher.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = education_teacher.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'education_teacher__update.html?id='+selected.id+'&random='+Math.random(), height: 500,width: 700
							,title: top.il8n.modify
							,isHidden: false
						});						
					}
				});
			}else if(permission[i].code=='1821'){
				//拥有 添加一个用户 的权限
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//education_teacher.insert();
						top.$.ligerDialog.open({ 
							url: 'education_teacher__insert.html?random='+Math.random(), height: 500,width: 700
							,title: top.il8n.add
						});
					}
				});
			}else if(permission[i].code=='1802'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected;
						if(education_teacher.grid.options.checkbox){
							selected = education_teacher.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = education_teacher.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}
						
						//window.open('education_teacher__view.html?id='+selected.id+'&id_person='+selected.id_person);

						
						top.$.ligerDialog.open({ 
							url: 'education_teacher__view.html?id='+selected.id+'&id_person='+selected.id_person+'&random='+Math.random(), height: 500,width: 700
							,title: top.il8n.view
							,isHidden: false
							,showMax: true
						});
							
					}
				});
			}else if(permission[i].code=='120208'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected;
						if(education_teacher.grid.options.checkbox){
							selected = education_teacher.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = education_teacher.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_group_2_user__tree.html?username='+selected.code+'&random='+Math.random(), height: 500,width: 400
							,title: top.il8n.education_teacher.updateUserGroup
							,isHidden: false
						});	
					}
				});
			}
		}
		
		$(document.body).ligerGrid(config);
	}
	
	,upload : function(){
		var dialog;
		if($.ligerui.get("education_teacher__grid_upload_d")){
			dialog = $.ligerui.get("education_teacher__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='education_teacher__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('education_teacher__grid_file'),
				action: '../php/myApp.php?class=education_teacher&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/education_teacher.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					education_teacher.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "education_teacher__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#education_teacher__grid_file"),
				modal : true
			});
		}
	}
	
	,download: function(){
		var dialog;
		if($.ligerui.get("education_teacher__grid_download_d")){
			dialog = $.ligerui.get("education_teacher__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='education_teacher__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "education_teacher__grid_download_d",
				width : 350,
				height : 200,
				target : $("#education_teacher__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=education_teacher&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					alert(response.msg);
				}else if(response.state==1){
					$("#education_teacher__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a><br/>");
				}
			},
			error : function(){
				
				alert(top.il8n.disConnect);
			}
		});		
	}
	
	/**
	 * 删除一个或多个用户
	 * */
	,delet: function(){
		selected = education_teacher.grid.getSelecteds();
		if(selected.length==0){alert(top.il8n.noSelect);return;}
		if(confirm(top.il8n.sureToDelete)){
			var ids = "";
			for(var i=0; i<selected.length; i++){
				ids += selected[i].id+","
			}
			ids = ids.substring(0,ids.length-1);				
			
			$.ajax({
				url: myAppServer() + "&class=education_teacher&function=delete",
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
						education_teacher.grid.loadData();
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
			afterInsert //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		
		var config = {
			id: 'education_teacher__addForm',
			fields: [
				 { display: top.il8n.education_teacher.code, name: "education_teacher__code",  type: "text",  validate: { required:true, minlength:3, maxlength:50} }
				,{ display: top.il8n.education_teacher.certificate, name: "education_teacher__certificate", newline: false, type: "text",  validate: { required:true, minlength:3, maxlength:50} }
				,{ display: top.il8n.education_teacher.title, name: "education_teacher__title",  type: "select", options :{data: education_teacher.config.title, valueField : "code" , textField: "value", slide: false } }			
				,{ display: top.il8n.education_teacher.type, name: "education_teacher__type", newline: false, type: "select", options :{data: education_teacher.config.type, valueField : "code" , textField: "value", slide: false },validate: { required:true }}				
				,{ display: top.il8n.education_teacher.honor, name: "education_teacher__honor",  type: "select", options :{data: education_teacher.config.honor, isMultiSelect: true, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.education_teacher.department, name: "education_teacher__department", newline: false,  type: "select", options :{data: education_teacher.config.department, valueField : "code" , textField: "name", slide: false },validate: { required:true } }
				
				,{ display: top.il8n.education_teacher.years, name: "education_teacher__years", type: "text",  validate: { required:true, digits: true} }
				,{ display: top.il8n.education_teacher.specialty, name: "education_teacher__specialty", newline: false, type: "text" }
				,{ name: "education_teacher__specialty_code",  type: "hidden" }
				
				,{ display: top.il8n.education_teacher.experience_work, name: "education_teacher__experience_work", width: 470, type: "textarea" }
				,{ display: top.il8n.education_teacher.experience_publish, name: "education_teacher__experience_publish", width: 470, type: "textarea" }
				,{ display: top.il8n.education_teacher.experience_project, name: "education_teacher__experience_project", width: 470, type: "textarea" }
				
				,{ display: top.il8n.education_teacher.photo_certificate, name: "education_teacher__photo_certificate", type: "text" }
				,{ display: top.il8n.education_teacher.photo_degree, name: "education_teacher__photo_degree", newline: false, type: "text" }
				
				,{ display: top.il8n.status, name: "status", type: "select" ,  options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.remark, name: "remark", type: "text", width: 470, newline: true }
			
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			$(dom).ligerForm(config);
			education_teacher.afterFormCreated();
			
			$(dom).append('<br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="education_teacher__submit" class="l-button l-button-submit" /></div>' );
			
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
					if(education_teacher.ajaxState)return;
					education_teacher.ajaxState = true;
					$("#education_teacher__submit").attr("value",top.il8n.waitting);
					
					$.ajax({
						url: myAppServer() + "&class=education_teacher&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 code: $.ligerui.get('education_teacher__code').getValue()
								,certificate: $.ligerui.get('education_teacher__certificate').getValue()
								,title: $.ligerui.get('education_teacher__title').getValue()
								,years: $.ligerui.get('education_teacher__years').getValue()
								,type: $.ligerui.get('education_teacher__type').getValue()
								,honor: $.ligerui.get('education_teacher__honor').getValue()
								,department: $.ligerui.get('education_teacher__department').getValue()
								,specialty: $.ligerui.get('education_teacher__specialty').getValue()
								,experience_work: $('#education_teacher__experience_work').val()
								,experience_publish: $('#education_teacher__experience_publish').val()
								,experience_project: $('#education_teacher__experience_project').val()
								,photo_certificate: $.ligerui.get('education_teacher__photo_certificate').getValue()
								,photo_degree: $.ligerui.get('education_teacher__photo_degree').getValue()

								,specialty_code: $('#education_teacher__specialty_code').val()
								
								,status: $.ligerui.get('status').getValue()
								,remark: $.ligerui.get('remark').getValue()								

							})
							
							//服务端权限验证所需
							,username: top.basic_user.username
							,session: MD5( top.basic_user.session +((new Date()).getHours()))
						},
						type: "POST",
						dataType: 'json',						
						success: function(response) {		
							//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
							if(response.state==1){
								alert(top.il8n.done);
								education_teacher.ajaxState = false;
								$("#education_teacher__submit").val(top.il8n.submit);	
								education_teacher.ajaxData = {
								      id_person: response.id_person
								     ,id_user: response.id_user
								};
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								education_teacher.ajaxState = false;
								$("#education_teacher__submit").val(top.il8n.submit);									
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
	
	,afterFormCreated: function(){		
		
		$.ligerui.get('education_teacher__photo_certificate').setDisabled();
		$.ligerui.get('education_teacher__photo_degree').setDisabled();
		
		var dom = $("#education_teacher__photo_certificate").parent().parent().next();
		$(dom).append('<a onclick="education_teacher.photo(\'education_teacher__photo_certificate\')" href="#"><img src="../file/icon16x16/doing.gif"></a>');
		var dom = $("#education_teacher__photo_degree").parent().parent().next();
		$(dom).append('<a onclick="education_teacher.photo(\'education_teacher__photo_degree\')" href="#"><img src="../file/icon16x16/doing.gif"></a>');
		
		var dom = $("#education_teacher__specialty").parent().parent().next();
		$(dom).append('<a onclick="education_teacher.specialtyDialog(\'education_teacher__specialty\')" href="#"><img src="../file/icon16x16/doing.gif"></a>');		
	}	
	
	,photo: function(id){
		$.ligerDialog.open({ 
			url: 'basic_parameter__photoUpload.html?id='+id+"&path="+$.ligerui.get(id).getValue(), height: 350,width: 400
			,isHidden: false
		});
	}
	
	,specialtyDialog: function(id){
		$.ligerDialog.open({ 
			 id: "EDU_BKZYML"
			,url: 'basic_parameter__EDU-BKZYML.html?id='+id, height: 350,width: 400
			,isHidden: false
		});
	}
	
	//AJAX 通信状态,如果为TRUE,则表示服务端还在通信中	
	,ajaxState: false 
	,ajaxData: null
	
	,afterInsert: function(){
		$('#buttons').empty();
		var htmlStr = '<input style="width:80%;" onclick="education_teacher.addExtend.person()" type="button" value="'+top.il8n.basic_person.basic_person+'"  class="l-button l-button-submit" />';
		htmlStr += '<input style="width:80%;" onclick="education_teacher.addExtend.user()" type="button" value="'+top.il8n.basic_user.basic_user+'" class="l-button l-button-submit" />';
			
		$('#buttons').append(htmlStr);
	}
	
	,addExtend: {
		person: function(){
			top.$.ligerDialog.open({ 
				url: 'basic_person__update.html?id='+education_teacher.ajaxData.id_person+'&random='+Math.random(), height: 540,width: 700
				,title: top.il8n.basic_person.basic_person
			});
		}
		,user: function(){
			top.$.ligerDialog.open({ 
				url: 'basic_user__update.html?id='+education_teacher.ajaxData.id_user+'&random='+Math.random(), height: 500,width: 400
				,title: top.il8n.basic_user.basic_user
			});
		}
	}
	
	,update: function(dom,afterAjax){
		var config = this.insert();
		$(dom).ligerForm(config);
		education_teacher.afterFormCreated();
		$(dom).append('<br/><br/><input style="width:80%" type="submit" value="'+top.il8n.modify+'" id="education_teacher__submit" class="l-button l-button-submit" />' );
		$(dom).append('<input style="width:80%" onclick="education_teacher.addExtend.person()" type="button" value="'+top.il8n.basic_person.basic_person+'" class="l-button l-button-submit" />' );
		$(dom).append('<input style="width:80%" onclick="education_teacher.addExtend.user()" type="button" value="'+top.il8n.basic_user.basic_user+'" class="l-button l-button-submit" />' );

		
		//从服务端读取信息,填充表单内容
		$.ajax({
			url: myAppServer() + "&class=education_teacher&function=view"
			,data: {
				id: getParameter("id", window.location.toString() )
				
				//服务端权限验证所需
				,personname: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    education_teacher.ajaxData = response;
			    
			    $.ligerui.get('education_teacher__code').setValue(response.code);
			    $.ligerui.get('education_teacher__department').setValue(response.department);
			    $.ligerui.get('education_teacher__certificate').setValue(response.certificate);
			    $.ligerui.get('education_teacher__title').setValue(response.title);
			    $.ligerui.get('education_teacher__years').setValue(response.years);
			    $.ligerui.get('education_teacher__type').setValue(response.type);
			    $.ligerui.get('education_teacher__honor').setValue(response.honor);
			    $.ligerui.get('education_teacher__specialty').setValue(response.specialty);
			    $('#education_teacher__specialty_code').val(response.specialty_code);
			    $('#education_teacher__experience_work').val(response.experience_work);
			    $('#education_teacher__experience_publish').val(response.experience_publish);
			    $('#education_teacher__experience_project').val(response.experience_project);
			    $.ligerui.get('education_teacher__photo_certificate').setValue(response.photo_certificate);
			    $.ligerui.get('education_teacher__photo_degree').setValue(response.photo_degree);
			    
			    $.ligerui.get('status').setValue(response.status);
			    $.ligerui.get('remark').setValue(response.remark);
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
				if(education_teacher.ajaxState)return;
				education_teacher.ajaxState = true;
				$("#education_teacher__submit").attr("value",top.il8n.waitting);
				
				//将被传递到后台,执行修改操作的数据
				var data = {
					//肯定包含 主键 id
				    id: getParameter("id", window.location.toString() )
				};
				//检查表单中,有没有被更改的内容.只将有更改的内容传递到服务端
				//其中,密码比较特殊


				if( $.ligerui.get('education_teacher__code').getValue() !=education_teacher.ajaxData.code  )
					data.code = $.ligerui.get('education_teacher__code').getValue();
				if( $.ligerui.get('education_teacher__department').getValue() !=education_teacher.ajaxData.department  )
					data.department = $.ligerui.get('education_teacher__department').getValue();
				if( $.ligerui.get('education_teacher__certificate').getValue() !=education_teacher.ajaxData.certificate  )
					data.certificate = $.ligerui.get('education_teacher__certificate').getValue();
				if( $.ligerui.get('education_teacher__title').getValue() !=education_teacher.ajaxData.title  )
					data.title = $.ligerui.get('education_teacher__title').getValue();
				if( $.ligerui.get('education_teacher__years').getValue() !=education_teacher.ajaxData.years  )
					data.years = $.ligerui.get('education_teacher__years').getValue();
				if( $.ligerui.get('education_teacher__type').getValue() !=education_teacher.ajaxData.type  )
					data.type = $.ligerui.get('education_teacher__type').getValue();
				if( $.ligerui.get('education_teacher__honor').getValue() !=education_teacher.ajaxData.honor  )
					data.honor = $.ligerui.get('education_teacher__honor').getValue();
				if( $.ligerui.get('education_teacher__specialty').getValue() !=education_teacher.ajaxData.specialty  ){
					data.specialty = $.ligerui.get('education_teacher__specialty').getValue();
					data.specialty_code = $('#education_teacher__specialty_code').val();
				}
				
				if( $('#education_teacher__experience_work').val() !=education_teacher.ajaxData.experience_work  )
					data.experience_work = $('#education_teacher__experience_work').val();
				if( $('#education_teacher__experience_publish').val() !=education_teacher.ajaxData.experience_publish  )
					data.experience_work = $('#education_teacher__experience_publish').val();
				if( $('#education_teacher__experience_project').val() !=education_teacher.ajaxData.experience_project  )
					data.experience_work = $('#education_teacher__experience_project').val();
				
				if( $.ligerui.get('education_teacher__photo_certificate').getValue() !=education_teacher.ajaxData.photo_certificate  )
					data.photo_certificate = $.ligerui.get('education_teacher__photo_certificate').getValue();
				if( $.ligerui.get('education_teacher__photo_degree').getValue() !=education_teacher.ajaxData.photo_degree  )
					data.photo_degree = $.ligerui.get('education_teacher__photo_degree').getValue();

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
					url: myAppServer() + "&class=education_teacher&function=update"
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
							education_teacher.ajaxState = false;
							$("#education_teacher__submit").val(top.il8n.submit);	
							alert(top.il8n.done);
							//如果参数中,有 回调函数,则执行
							if ( typeof(afterInsert) == "string" ){
								eval(afterInsert);
							}
						
						//服务端添加失败
						}else if(response.state==0){
							education_teacher.ajaxState = false;
							$("#education_teacher__submit").val(top.il8n.submit);									
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
	
	,searchOptions: {}		
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
					{ display: top.il8n.basic_person.name, name: "education_teacher__search_name", newline: true, type: "text" }
					,{ display: top.il8n.education_teacher.code, name: "education_teacher__search_code", newline: true, type: "text" }
					,{ display: top.il8n.type, name: "education_teacher__search_type", newline: true, type: "select", options :{data : education_teacher.config.type, valueField : "code" , textField: "value" } }
					,{ display: top.il8n.basic_person.birthday, name: "education_teacher__search_birthday_min", newline: true, type: "date" }
					,{ display: top.il8n.basic_person.birthday, name: "education_teacher__search_birthday_max", newline: true, type: "date" }
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
						$.ligerui.get("education_teacher__grid").options.parms.search = "{}";
						$.ligerui.get("education_teacher__grid").loadData();
						
						$.ligerui.get("education_teacher__search_name").setValue('');
						$.ligerui.get("education_teacher__search_code").setValue('');
						$.ligerui.get("education_teacher__search_type").setValue('');
						$.ligerui.get("education_teacher__search_birthday_min").setValue('');
						$.ligerui.get("education_teacher__search_birthday_max").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
						var data = {};
						var name = $.ligerui.get("education_teacher__search_name").getValue();
						var code = $.ligerui.get("education_teacher__search_code").getValue();
						var type = $.ligerui.get("education_teacher__search_type").getValue();
						var birthday_min = $.ligerui.get("education_teacher__search_birthday_min").getValue();
						var birthday_max = $.ligerui.get("education_teacher__search_birthday_max").getValue();
						
						if(name!="")data.name = name;
						if(code!="")data.code = code;
						if(type!="")data.type = type;
						if(birthday_min!="")data.birthday_min = birthday_min;
						if(birthday_max!="")data.birthday_max = birthday_max;
						
						$.ligerui.get("education_teacher__grid").options.parms = {
	
							username: top.basic_user.username
							,session: MD5( top.basic_user.session +((new Date()).getHours()))
							,search: $.ligerui.toJSON(data)
							
						};
						$.ligerui.get("education_teacher__grid").loadData();
				}}]
			});
		}
	}
	
	,view: function(){
		
		$.ajax({
			url: myAppServer() + "&class=education_teacher&function=view"
			,data: {
				id: getParameter("id", window.location.toString() )
				
				//服务端权限验证所需
				,personname: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
				//console.debug(response);
				$('#education_teacher__code').html(response.code);
				$('#education_teacher__certificate').html(response.certificate);
				
				for(var i=0;i<education_teacher.config.type.length;i++){
					if(response.type == education_teacher.config.type[i].code )
						$('#education_teacher__type').html(education_teacher.config.type[i].value);
				}
				
				for(var i=0;i<education_teacher.config.title.length;i++){
					if(response.title == education_teacher.config.title[i].code )
						$('#education_teacher__title').html(education_teacher.config.title[i].value);
				}	
				
				$('#education_teacher__experience_work').html(response.experience_work);
				$('#education_teacher__experience_publish').html(response.experience_publish);
				$('#education_teacher__experience_project').html(response.experience_project);				
			}
		});
		
		basic_person.loadConfig(function(){
			$.ajax({
				url: myAppServer() + "&class=basic_person&function=view"
				,data: {
					id: getParameter("id_person", window.location.toString() )
					
					//服务端权限验证所需
					,personname: top.basic_user.username
					,session: MD5( top.basic_user.session +((new Date()).getHours()))
				}
				,type: "POST"
				,dataType: 'json'						
				,success: function(response) {	
					$('#basic_person__name').html(response.name);
					$('#basic_person__degree_school').html(response.degree_school);
					$('#basic_person__birthday').html(response.birthday);
					$('#basic_person__cellphone').html(response.cellphone);					
					for(var i=0;i<basic_person.config.GB2261_1.length;i++){
						if(response.gender == basic_person.config.GB2261_1[i].code )
							$('#basic_person__gender').html(basic_person.config.GB2261_1[i].value);
					}
					for(var i=0;i<basic_person.config.GB4568.length;i++){
						if(response.degree == basic_person.config.GB4568[i].code )
							$('#basic_person__degree').html(basic_person.config.GB4568[i].value);
					}
					
					$('#basic_person__photo').attr("src",response.photo);
				}
			});	
		});

		
		
		var config = {
				id: 'education_teacher__addForm',
				
				fields: [
					 { display: top.il8n.basic_person.name, name: "basic_person__name" , type:'span' }
					,{ display: top.il8n.basic_person.gender, name: "basic_person__gender",  type:'span' }  
				          
					,{ display: top.il8n.education_teacher.code, name: "education_teacher__code" , type:'span' }
					,{ display: top.il8n.education_teacher.certificate, name: "education_teacher__certificate",  type:'span' }
					
					,{ display: top.il8n.education_teacher.type, name: "education_teacher__type" , type:'span' }
					,{ display: top.il8n.education_teacher.title, name: "education_teacher__title",  type:'span' }					
					
					,{ display: top.il8n.basic_person.degree, name: "basic_person__degree" , type:'span' }
					,{ display: top.il8n.basic_person.degree_school, name: "basic_person__degree_school", newline:false, type:'span' }
					
					,{ display: top.il8n.basic_person.birthday, name: "basic_person__birthday" , type:'span' }
					,{ display: top.il8n.basic_person.cellphone, name: "basic_person__cellphone", newline:false, type:'span' }					
					
					,{ display: top.il8n.education_teacher.experience_work, name: "education_teacher__experience_work", width: 470 , type:'spanarea'}
					,{ display: top.il8n.education_teacher.experience_publish, name: "education_teacher__experience_publish", width: 470 , type:'spanarea'}
					,{ display: top.il8n.education_teacher.experience_project, name: "education_teacher__experience_project", width: 470 , type:'spanarea'}										
				]
		};
		$(document.body).append("<div style='width:100%;text-align:center;font-size:20px;font-weight:bold;'>"+top.il8n.education_teacher.file+"</div><hr style='height:1.5px;color:balck' />");
		$(document.body).append("<form id='form'></form>");
		$(document.body).append("<img id='basic_person__photo' style='position:absolute;border:1px solid;left:400px;top:30px;widht:156px;height:167px;'  />");
		
		$("#form").ligerForm(config);   
	}
	
	
};