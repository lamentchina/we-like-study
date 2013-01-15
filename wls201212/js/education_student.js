/**
 * 学生模块的系统前端代码
 * 
 * @author wei1224hf@gmail.com
 * @version 201210
 * */
var education_student = {
	//用于做系统的版本匹配计算,用于系统程序自检,与业务无关

	
	/**
	 * 模块内部的配置文件
	 * 将从服务端读取
	 *   所有的下拉列表元素,那些都是存储在数据库的
	 *   当前用户的类型 学生 还是教师
	 * */
	 config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=education_student&function=loadConfig"
			,dataType: 'json'
			,data: {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
			}
			,success : function(response) {
				education_student.config = response;
				if ( typeof(afterAjax) == "string" ){
					eval(afterAjax);
				}
			}
			,error : function(){				
				alert(top.il8n.disConnect);
			}
		});	
	}
	
	/**
	 * 页面列表ligerUI控件
	 * 
	 * 学生自己看到的列,与教师看到的列,是不一样的
	 * 学生能够看到的列有:
	 *     学生姓名,班级,学号,性别,出生年月,班级内部职务(组织机构组内职务)
	 * 教师能够看到的列有:
	 *     姓名,学号,班级,性别,组内职务,高中毕业学校,班级排名,年级排名
	 *     
	 * 关于列表数据的后台查询权限:
	 *     学生,只能查看他所在用户组的组内同学.有可能是同班同学,有可能是其他的业余活动组
	 *         服务端按照用户的用户组分配来查询
	 *         首先查询学生所在的组织机构的学生,然后再查询 活动类型 用户组一样的
	 *     教师,
	 *         一般的任课老师,可以看到他分管内的所有学生
	 *         班主任仅仅是一个职务而已,班主任对所负责的班级,必定有任课关系
	 *         班主任可以对所负责的班级的学生资料,进行修改操作,管辖内的学生,显示的时候高亮处理
	 *     教师领导,
	 *         可以对学生信息执行 增删改 操作,并且可以查询到所有
	 * */
	,grid: function(){
		var gridColmuns = [
   			 { display: top.il8n.education_student.code, name: 'code',isSort : false, width:120 }
   			,{ display: top.il8n.education_student.name, name: 'name',isSort : false, width:100 }
   			,{ display: top.il8n.education_student.class_code, name: 'class_code',isSort : false, hide:true }
   			,{ display: top.il8n.education_student.class_name, name: 'class_name',isSort : false, width:100 }
   			,{ display: top.il8n.education_student.class_teacher_name, name: 'class_teacher_name',isSort : false, width:100 }
   			,{ display: top.il8n.education_student.class_teacher_code, name: 'class_teacher_code',isSort : false, hide:true  }
   			,{ display: top.il8n.education_student.class_teacher_id, name: 'class_teacher_id',isSort : false, hide:true  }
   			,{ display: top.il8n.education_student.class_manager, name: 'class_manager',isSort : false, width:50 }
   			,{ display: top.il8n.education_student.id_user, name: 'id_user',isSort : false, hide:true  }
   			,{ display: top.il8n.education_student.id_person, name: 'id_person',isSort : false , hide:true}
   			,{ display: top.il8n.education_student.scorerank, name: 'scorerank',isSort : false, width:50 }
   			,{ display: top.il8n.education_student.scorerank2, name: 'scorerank2',isSort : false, width:50 }
   			,{ display: top.il8n.education_student.scorerank3, name: 'scorerank3',isSort : false, width:50 }
   			
   			,{ display: top.il8n.status, name: 'status',isSort : false, hide:true  }
   			,{ display: top.il8n.type, name: 'type',isSort : false, hide:true  }
   			,{ display: top.il8n.id_creater, name: 'id_creater',isSort : false, hide:true  }
   			,{ display: top.il8n.id_creater_group, name: 'id_creater_group',isSort : false, hide:true  }
   			,{ display: top.il8n.time_created, name: 'time_created',isSort : false, hide:true  }
   			,{ display: top.il8n.id, name: 'id',isSort : false, hide:true  }

   			];
		var config = {
			id: 'education_student__grid'
			,height:'100%'
			,columns: gridColmuns
			,pageSize:20 
			,rownumbers:true
			,parms : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
			}
			,url: myAppServer() + "&class=education_student&function=grid"
			,method: "POST"				
			,toolbar: { items: []}
		};
		
		//配置列表表头的按钮,根据当前用户的权限来初始化
		var permission = top.basic_user.permission;
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='17'){
				permission = permission[i].children;
				break;
			}
		}
		
		for(var i=0;i<permission.length;i++){
			config.toolbar.items.push({line: true });
			config.toolbar.items.push({text: permission[i].name , img:permission[i].icon });
			if(permission[i].code=='1701'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};			
			if(permission[i].code=='1702'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};
			if(permission[i].code=='1711'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};			
			if(permission[i].code=='1712'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};
			if(permission[i].code=='1721'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};			
			if(permission[i].code=='1722'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};	
			if(permission[i].code=='1723'){
				config.toolbar.items[i].click = function(){
					education_student.search();
				}
			};
		}
		
		$(document.body).ligerGrid(config);
	}
	
	/**
	 * 上传一个 EXCEL 文件,批量导入学生信息
	 * 使用JQ的AJAX文件上传插件
	 * 可以一次性批量上传很多歌 XLS 文件
	 * */
	,upload : function(){
		var dialog;
		if($.ligerui.get("education_student__grid_upload_d")){
			dialog = $.ligerui.get("education_student__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='education_student__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('education_student__grid_file'),
				action: '../php/myApp.php?class=education_student&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.education_student.username,
					session: MD5( top.education_student.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/education_student.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					education_student.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "education_student__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#education_student__grid_file"),
				modal : true
			});
		}
	}
	
	/**
	 * 在当前查询条件下
	 * 导出前1000条数据,如果有1000条的话
	 * */
	,download: function(){
		var dialog;
		if($.ligerui.get("education_student__grid_download_d")){
			dialog = $.ligerui.get("education_student__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='education_student__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "education_student__grid_download_d",
				width : 350,
				height : 200,
				target : $("#education_student__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=education_student&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.education_student.username,
				session: MD5( top.education_student.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					alert(response.msg);
				}else if(response.state==1){
					$("#education_student__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a><br/>");
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
		selected = education_student.grid.getSelecteds();
		if(selected.length==0){alert(il8n.noSelect);return;}
		if(confirm(il8n.sureToDelete)){
			var ids = "";
			for(var i=0; i<selected.length; i++){
				ids += selected[i].id+","
			}
			ids = ids.substring(0,ids.length-1);				
			
			$.ajax({
				url: myAppServer() + "&class=education_student&function=delete",
				data: {
					ids: ids 
					
					//服务端权限验证所需
					,username: top.education_student.username
					,session: MD5( top.education_student.session +((new Date()).getHours()))
				},
				type: "POST",
				dataType: 'json',
				success: function(response) {
					if(response.state==1){
						education_student.grid.loadData();
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
			id: 'education_student__addForm',
			fields: [
				 { display: top.il8n.education_student.code, name: "education_student__code",  type: "text",  validate: { required:true, minlength:3, maxlength:10} }
				,{ display: top.il8n.education_student.class_code, name: "education_student__class_code",  type: "select", options :{data: education_student.config.department, valueField : "code" , textField: "name", slide: false }, validate: {required:true}, newline: false }
				,{ display: top.il8n.education_student.class_manager, name: "education_student__class_manager",  type: "select", options :{data: education_student.config.classmanager, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.education_student.intelligence, name: "education_student__intelligence",  type: "select", options :{data: education_student.config.intelligence, valueField : "code" , textField: "value", slide: false }, newline: false }

				,{ display: top.il8n.education_student.specialty, name: "education_student__specialty",  group: "&nbsp;",  type: "select", options :{data: education_student.config.specialty, isMultiSelect: true, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.education_student.hobby, name: "education_student__hobby",  type: "select", options :{data: education_student.config.hobby, isMultiSelect: true, valueField : "code" , textField: "value", slide: false }, newline: false }
				,{ display: top.il8n.education_student.characters, name: "education_student__characters",  type: "select", options :{data: education_student.config.characters, isMultiSelect: true, valueField : "code" , textField: "value", slide: false } }
				
				,{ display: top.il8n.education_student.growth, name: "education_student__growth", group: "&nbsp;", type: "text", width: 470, newline: true }
				,{ display: top.il8n.education_student.health, name: "education_student__health", type: "text", width: 470, newline: true }
				,{ display: top.il8n.education_student.healthdefect, name: "education_student__healthdefect", type: "text", width: 470, newline: true }
				,{ display: top.il8n.education_student.mentalhealth, name: "education_student__mentalhealth", type: "text", width: 470, newline: true }
				
				,{ display: top.il8n.education_student.attitude_learn, name: "education_student__attitude_learn", group: "&nbsp;", type: "select", options :{data: education_student.config.alearn, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.education_student.attitude_life, name: "education_student__attitude_life",  type: "select", options :{data: education_student.config.alife,  valueField : "code" , textField: "value", slide: false }, newline: false }
				,{ display: top.il8n.education_student.attitude_teacher, name: "education_student__attitude_teacher",  type: "select", options :{data: education_student.config.ateacher, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.education_student.attitude_classmate, name: "education_student__attitude_classmate",  type: "select", options :{data: education_student.config.aclassmate, valueField : "code" , textField: "value", slide: false }, newline: false }
				,{ display: top.il8n.education_student.attitude_oppositesex, name: "education_student__attitude_oppositesex",  type: "select", options :{data: education_student.config.aoppositesex, valueField : "code" , textField: "value", slide: false } }

				,{ display: top.il8n.education_student.junior_school, name: "education_student__junior_school", group: "&nbsp;", type: "text" }
				,{ display: top.il8n.education_student.junior_graduated, name: "education_student__junior_graduated",  type: "text", newline: false }
				,{ display: top.il8n.education_student.junior_scores, name: "education_student__junior_scores",  type: "text" }
				,{ display: top.il8n.education_student.junior_rank, name: "education_student__junior_rank",  type: "text", newline: false }
				
				,{ display: top.il8n.education_student.parents_name, name: "education_student__parents_name", group: "&nbsp;", type: "text" }
				,{ display: top.il8n.education_student.parents_cellphone, name: "education_student__parents_cellphone",  type: "text", newline: false }
				
				,{ display: top.il8n.status, name: "status", type: "select" , group: "&nbsp;", options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.remark, name: "remark", type: "text", width: 470, newline: true }
			
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			$(dom).ligerForm(config);
			
			//ligerUI 浏览器不兼容 BUG 处理
			if($.browser.msie && top != self){
				$.ligerui.get('education_student__type').setData(top.il8n.education_student__types);
				$.ligerui.get('education_student__status').setData(top.il8n.education_student__status);
			}
			
			$(dom).append('<br/><br/><br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="education_student__submit" class="l-button l-button-submit" /></div>' );
			
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
					if(education_student.ajaxState)return;
					education_student.ajaxState = true;
					$("#education_student__submit").attr("value",top.il8n.waitting);
					
					$.ajax({
						url: myAppServer() + "&class=education_student&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 code: $.ligerui.get('education_student__code').getValue()
								,class_code: $.ligerui.get('education_student__class_code').getValue()
								,class_manager: $.ligerui.get('education_student__class_manager').getValue()
								,intelligence: $.ligerui.get('education_student__intelligence').getValue()
								
								,specialty: $.ligerui.get('education_student__specialty').getValue()
								,hobby: $.ligerui.get('education_student__hobby').getValue()
								,characters: $.ligerui.get('education_student__characters').getValue()
								
								,growth: $.ligerui.get('education_student__growth').getValue()
								,health: $.ligerui.get('education_student__health').getValue()
								,healthdefect: $.ligerui.get('education_student__healthdefect').getValue()
								,mentalhealth: $.ligerui.get('education_student__mentalhealth').getValue()
								
								,attitude_life: $.ligerui.get('education_student__attitude_life').getValue()
								,attitude_learn: $.ligerui.get('education_student__attitude_learn').getValue()
								,attitude_teacher: $.ligerui.get('education_student__attitude_teacher').getValue()
								,attitude_classmate: $.ligerui.get('education_student__attitude_classmate').getValue()
								,attitude_oppositesex: $.ligerui.get('education_student__attitude_oppositesex').getValue()
								
								,junior_school: $.ligerui.get('education_student__junior_school').getValue()
								,junior_graduated: $.ligerui.get('education_student__junior_graduated').getValue()
								,junior_scores: $.ligerui.get('education_student__junior_scores').getValue()
								,junior_rank: $.ligerui.get('education_student__junior_rank').getValue()
								,parents_name: $.ligerui.get('education_student__parents_name').getValue()
								,parents_cellphone: $.ligerui.get('education_student__parents_cellphone').getValue()
								
								,status: $.ligerui.get('status').getValue()
								,remark: $.ligerui.get('remark').getValue()								

							})
							
							//服务端权限验证所需
							,username: top.education_student.username
							,session: MD5( top.education_student.session +((new Date()).getHours()))
						},
						type: "POST",
						dataType: 'json',						
						success: function(response) {		
							//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
							if(response.state==1){
								alert(top.il8n.done);
								education_student.ajaxState = false;
								$("#education_student__submit").val(top.il8n.submit);	
								education_student.ajaxData = {
								      id_person: response.id_person
								     ,id_user: response.id_user
								};
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								education_student.ajaxState = false;
								$("#education_student__submit").val(top.il8n.submit);									
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
	
	//AJAX 通信状态,如果为TRUE,则表示服务端还在通信中	
	,ajaxState: false 
	,ajaxData: null
	
	,afterInsert: function(){
		$('#buttons').empty();
		var htmlStr = '<input style="width:100px;" onclick="education_student.addExtend.person()" type="button" value="'+top.il8n.basic_person.basic_person+'" id="education_student__submit" class="l-button l-button-submit" />';
		htmlStr += '<input style="width:100px;" onclick="education_student.addExtend.user()" type="button" value="'+top.il8n.basic_user.basic_user+'" id="education_student__submit" class="l-button l-button-submit" />';
			
		$('#buttons').append(htmlStr);
	}
	
	,addExtend: {
		person: function(){
			top.$.ligerDialog.open({ 
				url: 'basic_person__update.html?id='+education_student.ajaxData.id_person+'&random='+Math.random(), height: 540,width: 700
				,title: top.il8n.basic_person.basic_person
			});
		}
		,user: function(){
			top.$.ligerDialog.open({ 
				url: 'basic_user__update.html?id='+education_student.ajaxData.id_user+'&random='+Math.random(), height: 500,width: 400
				,title: top.il8n.basic_user.basic_user
			});
		}
	}
	
	,update: function(dom,afterAjax){
		var config = this.insert();
		$(dom).ligerForm(config);
		
		$(dom).append('<br/><br/><br/><br/><input type="submit" value="'+top.il8n.modify+'" id="education_student__submit" class="l-button l-button-submit" />' );
		
		
		//从服务端读取信息,填充表单内容
		$.ajax({
			url: myAppServer() + "&class=education_student&function=view"
			,data: {
				id: getParameter("id", window.location.toString() )
				
				//服务端权限验证所需
				,personname: top.education_student.username
				,session: MD5( top.education_student.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    education_student.ajaxData = response;
			    $.ligerui.get('education_student__code').setValue(response.code);
			    $.ligerui.get('education_student__class_code').setValue(response.class_code);
			    $.ligerui.get('education_student__specialty').setValue(response.specialty);
			    $.ligerui.get('education_student__hobby').setValue(response.hobby);
			    $.ligerui.get('education_student__characters').setValue(response.characters);
			    $.ligerui.get('education_student__growth').setValue(response.growth);
			    $.ligerui.get('education_student__health').setValue(response.health);
			    $.ligerui.get('education_student__healthdefect').setValue(response.healthdefect);
			    $.ligerui.get('education_student__mentalhealth').setValue(response.mentalhealth);
			    $.ligerui.get('education_student__attitude_life').setValue(response.attitude_life);
			    $.ligerui.get('education_student__attitude_learn').setValue(response.attitude_learn);
			    $.ligerui.get('education_student__attitude_teacher').setValue(response.attitude_teacher);
			    $.ligerui.get('education_student__attitude_classmate').setValue(response.attitude_classmate);
			    $.ligerui.get('education_student__attitude_oppositesex').setValue(response.attitude_oppositesex);
			    $.ligerui.get('education_student__intelligence').setValue(response.intelligence);
			    $.ligerui.get('education_student__class_manager').setValue(response.class_manager);
			    $.ligerui.get('education_student__junior_school').setValue(response.junior_school);
			    $.ligerui.get('education_student__junior_graduated').setValue(response.junior_graduated);
			    $.ligerui.get('education_student__junior_scores').setValue(response.junior_scores);
			    $.ligerui.get('education_student__junior_rank').setValue(response.junior_rank);
			    $.ligerui.get('education_student__parents_name').setValue(response.parents_name);
			    $.ligerui.get('education_student__parents_cellphone').setValue(response.parents_cellphone);

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
				if(education_student.ajaxState)return;
				education_student.ajaxState = true;
				$("#education_student__submit").attr("value",top.il8n.waitting);
				
				//将被传递到后台,执行修改操作的数据
				var data = {
					//肯定包含 主键 id
				    id: getParameter("id", window.location.toString() )
				};
				//检查表单中,有没有被更改的内容.只将有更改的内容传递到服务端
				//其中,密码比较特殊
				if( $.ligerui.get('education_student__password').getValue() != '000' )
					data.password = $.ligerui.get('education_student__password').getValue();
				if(education_student.ajaxData.email != $.ligerui.get('education_student__email').getValue() )
					data.email = $.ligerui.get('education_student__email').getValue();
				if(education_student.ajaxData.type != $.ligerui.get('education_student__type').getValue() )
					data.type = $.ligerui.get('education_student__type').getValue();
				if(education_student.ajaxData.cellphone != $.ligerui.get('education_student__cellphone').getValue() )
					data.cellphone = $.ligerui.get('education_student__cellphone').getValue();
				if(education_student.ajaxData.status != $.ligerui.get('education_student__status').getValue() )
					data.status = $.ligerui.get('education_student__status').getValue();
				if(education_student.ajaxData.money != $.ligerui.get('education_student__money').getValue() )
					data.money = $.ligerui.get('education_student__money').getValue();
				if(education_student.ajaxData.money2 != $.ligerui.get('education_student__money2').getValue() )
					data.money2 = $.ligerui.get('education_student__money2').getValue();
				if(education_student.ajaxData.money3 != $.ligerui.get('education_student__money3').getValue() )
					data.money3 = $.ligerui.get('education_student__money3').getValue();
				if(education_student.ajaxData.remark != $.ligerui.get('education_student__remark').getValue() )
					data.remark = $.ligerui.get('education_student__remark').getValue();							
				
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
					url: myAppServer() + "&class=education_student&function=update"
					,data: {
						json: $.ligerui.toJSON(data)
						
						//服务端权限验证所需
						,personname: top.education_student.username
						,session: MD5( top.education_student.session +((new Date()).getHours()))
					}
					,type: "POST"
					,dataType: 'json'						
					,success: function(response) {		
						//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
						if(response.state==1){
							education_student.ajaxState = false;
							$("#education_student__submit").val(top.il8n.submit);	
							alert(top.il8n.done);
							//如果参数中,有 回调函数,则执行
							if ( typeof(afterInsert) == "string" ){
								eval(afterInsert);
							}
						
						//服务端添加失败
						}else if(response.state==0){
							education_student.ajaxState = false;
							$("#education_student__submit").val(top.il8n.submit);									
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
					 { display: top.il8n.education_student.username, name: "education_student__search_username", newline: false, type: "text" }
					,{ display: top.il8n.type, name: "education_student__search_type", newline: true, type: "select", options :{data : top.il8n.education_student__types, valueField : "code" , textField: "value" } }
					,{ display: top.il8n.status, name: "education_student__search_status", newline: true, type: "select", options :{data : top.il8n.education_student__status, valueField : "code" , textField: "value" } }
					,{ display: top.il8n.education_student.groups, name: "education_student__search_groups", newline: true, type: "text" }
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
						$.ligerui.get("education_student__grid").options.parms.search = "{}";
						$.ligerui.get("education_student__grid").loadData();
						
						$.ligerui.get("education_student__search_username").setValue('');
						$.ligerui.get("education_student__search_type").setValue('');
						$.ligerui.get("education_student__search_status").setValue('');
						$.ligerui.get("education_student__search_money").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
						var data = {};
						var username = $.ligerui.get("education_student__search_username").getValue();
						var type = $.ligerui.get("education_student__search_type").getValue();
						var status = $.ligerui.get("education_student__search_status").getValue();
						var money = $.ligerui.get("education_student__search_money").getValue();
						var groups = $.ligerui.get("education_student__search_groups").getValue();
						
						if(username!="")data.username = username;
						if(type!="")data.type = type;
						if(status!="")data.status = status;
						if(groups!="")data.groups = groups;
						if(money!=0)data.money = money;
						
						$.ligerui.get("education_student__grid").options.parms = {
	
							username: top.education_student.username
							,session: MD5( top.education_student.session +((new Date()).getHours()))
							,search: $.ligerui.toJSON(data)
							
						};
						$.ligerui.get("education_student__grid").loadData();
				}}]
			});
		}
	}
	
	/**
	 * 查看一个学生的档案
	 * 在查看的过程中,需要根据当前登录用的身份:
	 *  是本人,
	 *  不是本人
	 *    是老师
	 *      是任课老师
	 *      是班主任
	 *      是教务处领导
	 *      是其他的普通教师
	 *    是同学
	 *      是同班同学
	 *      是我参加的其他娱乐团队的同学
	 *      是其他班级的,一般的学生
	 * */
	,view: function(){
		
	}
};