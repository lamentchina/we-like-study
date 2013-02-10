var education_question = {

	version: "2012X1"
		
	,id : 0 
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			 url: myAppServer()+ "&class=education_question&function=loadConfig"
			,dataType: 'json'
            ,type: "POST"
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
				education_question.config = response;
				if ( typeof(afterAjax) == "string" ){
					eval(afterAjax);
				}else if( typeof(afterAjax) == "function"){
					afterAjax();
				}
			}
			,error : function(){				
				alert(top.il8n.disConnect);
			}
		});	
	}

	,grid: function(){
		var config = {
			height:'100%',
			columns: [
				{ display: top.il8n.title, name: 'title', align: 'left', width: 170, minWidth: 60 }
				,{ display: top.il8n.education_question.subject, name: 'subject_name',isSort : false }		
				,{ display: top.il8n.author, name: 'teacher_name' ,width: 90}
				,{ display: top.il8n.time_created, name: 'time_created'}
				,{ display: top.il8n.type, name: 'type_'}
			],  pageSize:20 ,rownumbers:true,
			url : myAppServer() + "&class=education_question&function=grid",
			method  : "POST",
			id : "education_question__grid",
			parms : {
                 username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
			},
			toolbar: { items: [] }
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
						education_question.search();
					}
				});
			}else if(permission[i].code=='130202'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						//education_question.view();
						var selected;
						if($.ligerui.get('education_question__grid').options.checkbox){
							selected = $.ligerui.get('education_question__grid').getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = $.ligerui.get('education_question__grid').getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'education_question__view.html?id='+selected.id+'&random='+Math.random(), height: 550,width: 750
							,title: top.il8n.details
							,isHidden: false
						});							
					}
				});
			}else if(permission[i].code=='130211'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_question.upload();
					}
				});
			}else if(permission[i].code=='130212'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_question.download();	
					}
				});
			}else if(permission[i].code=='130221'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						//education_question.insert();	
						top.$.ligerDialog.open({ 
							url: 'education_question__insert.html?random='+Math.random(), height: 550,width: 700
							,title: top.il8n.add
						});
					}
				});
			}else if(permission[i].code=='130222'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_question.delet();							
					}
				});
			}else if(permission[i].code=='130223'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						//education_question.update();
						var selected;
						if($.ligerui.get('education_question__grid').options.checkbox){
							selected = $.ligerui.get('education_question__grid').getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = $.ligerui.get('education_question__grid').getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'education_question__update.html?id='+selected.id+'&random='+Math.random(), height: 550,width: 700
							,title: top.il8n.modify
							,isHidden: false
						});							
					}
				});
			}else if(permission[i].code=='130290'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_question.paper();	
					}
				});
			}
		}
	
		$(document.body).ligerGrid(config);
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
			id: 'education_question__addForm',
			fields: [
				 { display: top.il8n.type, name: "education_question__type",  type: "select", options :{ data: education_question.config.type, valueField : "code" , textField: "value", slide: false ,onSelected: function(){alert(1)} },validate: { required:true }}				
				,{ display: top.il8n.education_question.type2, name: "education_question__type2",newline: false,  type: "text" }
				,{ display: top.il8n.title, name: "education_question__title",  width: 470, type: "textarea" , validate: { required:true } }
				,{ display: top.il8n.education_question.description, name: "education_question__description", width: 470, type: "text" }
				                                                                                       						            
				,{ display: top.il8n.education_question.answer, name: "education_question__answer", type: "text" }
				
				,{ display: top.il8n.education_question.layout, name: "education_question__layout",newline: false, type: "select", options :{data: education_question.config.layout, valueField : "code" , textField: "value", slide: false } }
				
				,{ display: top.il8n.education_question.optionlength, name: "education_question__optionlength",  type: "number" }
				,{ display: top.il8n.education_question.cent, name: "education_question__cent", newline: false,  type: "number" }
				
				,{ display: top.il8n.education_question.path_listen, name: "education_question__path_listen",  type: "text" }
				,{ display: top.il8n.education_question.path_image, name: "education_question__path_image", newline: false, type: "text" }
				
				,{ display: top.il8n.education_question.option+"A", name: "education_question__option1", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"B", name: "education_question__option2", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"C", name: "education_question__option3", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"D", name: "education_question__option4", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"E", name: "education_question__option5", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"F", name: "education_question__option6", width: 470, type: "text" }
				,{ display: top.il8n.education_question.option+"G", name: "education_question__option7", width: 470, type: "text" }
				
				,{ display: top.il8n.education_question.subject, name: "education_question__subject", type: "select", options :{data: education_question.config.subject, valueField : "code" , textField: "value", slide: false } }
				
				,{ display: top.il8n.status, name: "status", type: "select" ,newline: false,  options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.remark, name: "remark", type: "text", width: 470, newline: true }
			
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			$(dom).ligerForm(config);			
			$(dom).append('<br/><br/><br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="education_question__submit" class="l-button l-button-submit" /></div>' );
			education_question.afterFormCreated();
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
					if(education_question.ajaxState)return;
					education_question.ajaxState = true;
					$("#education_question__submit").attr("value",top.il8n.waitting);
					
					$.ajax({
						url: myAppServer() + "&class=education_question&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 type: $.ligerui.get('education_question__type').getValue()
								,type2: $.ligerui.get('education_question__type2').getValue()
								,title: $.ligerui.get('education_question__title').getValue()
								,description: $.ligerui.get('education_question__description').getValue()
								,answer: $.ligerui.get('education_question__answer').getValue()
								,layout: $.ligerui.get('education_question__layout').getValue()
								,optionlength: $.ligerui.get('education_question__optionlength').getValue()
								,cent: $.ligerui.get('education_question__cent').getValue()
								,path_listen: $.ligerui.get('education_question__path_listen').getValue()
								,path_image: $.ligerui.get('education_question__path_image').getValue()
								
								,option1: $.ligerui.get('education_question__option1').getValue()
								,option2: $.ligerui.get('education_question__option2').getValue()
								,option3: $.ligerui.get('education_question__option3').getValue()
								,option4: $.ligerui.get('education_question__option4').getValue()
								,option5: $.ligerui.get('education_question__option5').getValue()
								,option6: $.ligerui.get('education_question__option6').getValue()
								,option7: $.ligerui.get('education_question__option7').getValue()
								
								,subject: $.ligerui.get('education_question__subject').getValue()

								,status: $.ligerui.get('status').getValue()
								,remark: $.ligerui.get('remark').getValue()	
								
								,id_creater: top.basic_user.loginData.id
								,id_creater_group: top.basic_user.loginData.id_group
								,author: top.basic_user.loginData.name

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
								education_question.ajaxState = false;
								$("#education_question__submit").val(top.il8n.submit);	
								
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								education_question.ajaxState = false;
								$("#education_question__submit").val(top.il8n.submit);									
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
	
	,update: function(dom,afterAjax){
		var config = this.insert();
		$(dom).ligerForm(config);
		
		$(dom).append('<br/><br/><br/><input style="width:150px;" type="submit" value="'+top.il8n.modify+'" id="education_question__submit" name="education_question__submit" class="l-button l-button-submit" />' );
		education_question.afterFormCreated();
		//从服务端读取信息,填充表单内容
		$.ajax({
			 url: myAppServer() + "&class=education_question&function=view"
			,data: {
				id: getParameter("id", window.location.toString() )
				
				//服务端权限验证所需
				,personname: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    education_question.ajaxData = response;
			    
			    $.ligerui.get('education_question__type').setValue(response.type);
			    $.ligerui.get('education_question__type2').setValue(response.type2);
			    $.ligerui.get('education_question__title').setValue(response.title);
			    $.ligerui.get('education_question__description').setValue(response.description);
			    $.ligerui.get('education_question__answer').setValue(response.answer);
			    $.ligerui.get('education_question__layout').setValue(response.layout);
			    $.ligerui.get('education_question__optionlength').setValue(response.optionlength);
			    $.ligerui.get('education_question__cent').setValue(response.cent);
			    $.ligerui.get('education_question__path_listen').setValue(response.path_listen);
			    $.ligerui.get('education_question__path_image').setValue(response.path_image);
			    $.ligerui.get('education_question__option1').setValue(response.option1);
			    $.ligerui.get('education_question__option2').setValue(response.option2);
			    $.ligerui.get('education_question__option3').setValue(response.option3);
			    $.ligerui.get('education_question__option4').setValue(response.option4);
			    $.ligerui.get('education_question__option5').setValue(response.option5);
			    $.ligerui.get('education_question__option6').setValue(response.option6);
			    $.ligerui.get('education_question__option7').setValue(response.option7);
			    $.ligerui.get('education_question__subject').setValue(response.subject);
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
				if(education_question.ajaxState)return;
				education_question.ajaxState = true;
				$("#education_question__submit").attr("value",top.il8n.waitting);
				
				//将被传递到后台,执行修改操作的数据
				var data = {
					//肯定包含 主键 id
				    id: getParameter("id", window.location.toString() )
				};
				//检查表单中,有没有被更改的内容.只将有更改的内容传递到服务端
				
				if( $.ligerui.get('education_question__type').getValue() !=education_question.ajaxData.type  )
					data.type = $.ligerui.get('education_question__type').getValue();
				if( $.ligerui.get('education_question__type2').getValue() !=education_question.ajaxData.type2  )
					data.type2 = $.ligerui.get('education_question__type2').getValue();
				if( $.ligerui.get('education_question__title').getValue() !=education_question.ajaxData.title  )
					data.title = $.ligerui.get('education_question__title').getValue();
				if( $.ligerui.get('education_question__description').getValue() !=education_question.ajaxData.description  )
					data.description = $.ligerui.get('education_question__description').getValue();
				if( $.ligerui.get('education_question__answer').getValue() !=education_question.ajaxData.answer  )
					data.answer = $.ligerui.get('education_question__answer').getValue();
				if( $.ligerui.get('education_question__layout').getValue() !=education_question.ajaxData.layout  )
					data.layout = $.ligerui.get('education_question__layout').getValue();
				if( $.ligerui.get('education_question__optionlength').getValue() !=education_question.ajaxData.optionlength  )
					data.optionlength = $.ligerui.get('education_question__optionlength').getValue();
				if( $.ligerui.get('education_question__cent').getValue() !=education_question.ajaxData.cent  )
					data.cent = $.ligerui.get('education_question__cent').getValue();
				if( $.ligerui.get('education_question__path_listen').getValue() !=education_question.ajaxData.path_listen  )
					data.path_listen = $.ligerui.get('education_question__path_listen').getValue();
				if( $.ligerui.get('education_question__path_image').getValue() !=education_question.ajaxData.path_image  )
					data.path_image = $.ligerui.get('education_question__path_image').getValue();
				
				if( $.ligerui.get('education_question__option1').getValue() !=education_question.ajaxData.option1  )
					data.option1 = $.ligerui.get('education_question__option1').getValue();
				if( $.ligerui.get('education_question__option2').getValue() !=education_question.ajaxData.option2  )
					data.option2 = $.ligerui.get('education_question__option2').getValue();
				if( $.ligerui.get('education_question__option3').getValue() !=education_question.ajaxData.option3  )
					data.option3 = $.ligerui.get('education_question__option3').getValue();
				if( $.ligerui.get('education_question__option4').getValue() !=education_question.ajaxData.option4  )
					data.option4 = $.ligerui.get('education_question__option4').getValue();
				if( $.ligerui.get('education_question__option5').getValue() !=education_question.ajaxData.option5  )
					data.option5 = $.ligerui.get('education_question__option5').getValue();
				if( $.ligerui.get('education_question__option6').getValue() !=education_question.ajaxData.option6  )
					data.option6 = $.ligerui.get('education_question__option6').getValue();
				if( $.ligerui.get('education_question__option7').getValue() !=education_question.ajaxData.option7  )
					data.option7 = $.ligerui.get('education_question__option7').getValue();
				if( $.ligerui.get('education_question__subject').getValue() !=education_question.ajaxData.subject  )
					data.subject = $.ligerui.get('education_question__subject').getValue();
				if( $.ligerui.get('status').getValue() !=education_question.ajaxData.status  )
					data.status = $.ligerui.get('status').getValue();
				if( $.ligerui.get('remark').getValue() !=education_question.ajaxData.remark  )
					data.remark = $.ligerui.get('remark').getValue();

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
					url: myAppServer() + "&class=education_question&function=update"
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
							education_question.ajaxState = false;
							$("#education_question__submit").val(top.il8n.submit);	
							alert(top.il8n.done);
							//如果参数中,有 回调函数,则执行
							if ( typeof(afterInsert) == "string" ){
								eval(afterInsert);
							}
						
						//服务端添加失败
						}else if(response.state==0){
							education_question.ajaxState = false;
							$("#education_question__submit").val(top.il8n.submit);									
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
	
	,afterFormCreated: function(){		
		
		$.ligerui.get('education_question__path_listen').setDisabled();
		$.ligerui.get('education_question__path_image').setDisabled();
		
		var dom = $("#education_question__path_listen").parent().parent().next();
		$(dom).append('<a onclick="education_question.mp3(\'education_question__path_listen\')" href="#"><img src="../file/icon16x16/doing.gif"></a>');
		
		var dom = $("#education_question__path_image").parent().parent().next();
		$(dom).append('<a onclick="education_question.photo(\'education_question__path_image\')" href="#"><img src="../file/icon16x16/doing.gif"></a>');
		
	}
	
	,photo: function(id){
		$.ligerDialog.open({ 
			url: 'basic_parameter__photoUpload.html?id='+id+"&path="+$.ligerui.get(id).getValue(), height: 350,width: 400
			,isHidden: false
		});
	}
	
	,mp3: function(id){
		$.ligerDialog.open({ 
			url: 'basic_parameter__mp3Upload.html?id='+id+"&path="+$.ligerui.get(id).getValue(), height: 350,width: 400
			,isHidden: false
		});
	}
	
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = $.ligerui.get('education_question__grid').getSelecteds();
		//如果一行都没有选中,就报错并退出函数
		if(selected.length==0){alert(top.il8n.noSelect);return;}
		//弹框让用户最后确认一下,是否真的需要删除.一旦删除,数据将不可恢复
		if(confirm(top.il8n.sureToDelete)){
			var ids = "";
			//遍历每一行元素,获得 id 
			for(var i=0; i<selected.length; i++){
				ids += selected[i].id+","
			}
			ids = ids.substring(0,ids.length-1);				
			
			$.ajax({
				 url: myAppServer() + "&class=education_question&function=delete"
				,data: {
					ids: ids 
					
					//服务端权限验证所需
					,username: top.basic_user.username
					,session: MD5( top.basic_user.session +((new Date()).getHours()))
				},
				type: "POST",
				dataType: 'json',
				success: function(response) {
					if(response.state==1){
						$.ligerui.get('education_question__grid').loadData();
					}
				},
				error : function(){
					//网络通信失败,则删除按钮再也不能点了
					alert(top.il8n.disConnect);
				}
			});				
		}		
	}	
	
	,searchOptions : {}
	
	,search : function(){
		var formD;
		if($.ligerui.get("formD")){
			formD = $.ligerui.get("formD");
			formD.show();
		}else{
			var form = $("<form id='form'></form>");
			$(form).ligerForm({
				inputWidth: 170, labelWidth: 90, space: 40,
				fields: [
				     { display: top.il8n.title, name: "search_title", newline: false, type: "text" }
					,{ display: top.il8n.education_subject.subject, name: "search_subject", newline: true, type: "select", options: { data:education_question.config.subject, valueField : "code" , textField : "value",slide:false } }
					,{ display: top.il8n.type, name: "search_type", newline: true, type: "select", options: { data:education_question.config.type, valueField : "code" , textField : "value",slide:false } }
				]
			}); 
			$.ligerDialog.open({
				id : "formD",
				width : 350,
				height : 150,
				content : form,
				title : top.il8n.search,
				buttons : [
				    //清空查询条件
					{text:top.il8n.clear,onclick:function(){
						$.ligerui.get('education_question__grid').options.parms.search = "{}";
						$.ligerui.get('education_question__grid').loadData();
						
						$.ligerui.get("search_title").setValue('');
						$.ligerui.get("search_subject").setValue('');
						$.ligerui.get("search_type").setValue('');
					}},
					//提交查询条件
				    {text: top.il8n.search, onclick: function(){
				    	
				    	$.ligerui.get('education_question__grid').options.parms.search =  $.ligerui.toJSON({
							 title : $.ligerui.get("search_title").getValue()
							,subject : $.ligerui.get("search_subject").getValue()
							,type : $.ligerui.get("search_type").getValue()
						});
				    	$.ligerui.get('education_question__grid').loadData();
				}}]
			});
		}
	}
	
	,questionObj: {}
	,view: function(){
		var id = getParameter("id", window.location.toString() );
		$.ajax({
			 url: myAppServer() + "&class=education_question&function=view"
			,data: {
                 username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
                ,id: id
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
				
                if(response.type==1){//单项选择题
                	education_question.questionObj = new question_choice();
                	education_question.questionObj.optionlength = response.optionlength;
                	education_question.questionObj.options = [];
                    for(var ii=1;ii<=parseInt(response.optionlength);ii++){
                    	eval("education_question.questionObj.options.push(response.option"+ii+")");
                    }
                    education_question.questionObj.index = "";
                    education_question.questionObj.layout = response.layout;
                    education_question.questionObj.title = response.title;                   
                }
                else if(response.type==2){//多项选择题
                	education_question.questionObj = new question_multichoice();
                	education_question.questionObj.optionlength = response.optionlength;
                	education_question.questionObj.options = [];
                    for(var ii=1;ii<=parseInt(response.optionlength);ii++){
                    	eval("education_question.questionObj.options.push(response.option"+ii+")");
                    }
                    education_question.questionObj.index = "";
                    education_question.questionObj.layout = response.layout;
                    education_question.questionObj.title = response.title;                    
                }
                else if(response.type==3){//判断题
                	
                	education_question.questionObj = new question_check();
                    education_question.questionObj.index = "";
                    education_question.questionObj.title = response.title;                    
                }else{
                	return;
                }
                
                education_question.questionObj.type = response.type;
                education_question.questionObj.path_listen = response.path_listen;
                education_question.questionObj.cent = response.cent;
                education_question.questionObj.id = response.id;
                education_question.questionObj.id_parent = response.id_parent;
                education_question.questionObj.mode = 1;
                education_question.questionObj.description = response.description;
                education_question.questionObj.answer = response.answer;
                    
                $(document.body).append("<div id = 'wls_quiz_main'></div>");
  
                education_question.questionObj.initDom();
                education_question.questionObj.submitButton();
			}
		});
	}
};


/**
 * 题目来源于一张卷子(paper);
 * 每一道题都有 题目说明;解题思路;答案 等属性
 * 还有 初始化DOM;显示解题思路 等函数;
 * 不过部分函数跟属性会在扩展题型中添加
 * 这里是父类;给出了一道题所必须要有的公共属性
 * */
var question = function() {

    this.id = null;               //索引编号;在一张卷子里的题目序号;比如 1 2 3 4 5
	this.id_parent = null;        //在'阅读理解';'完型填空';'短文听力'等大题中使用;让子题目指向母题目
	this.id_paper = null;    	  //这个题目属于哪张试卷.WLS系统中;每个题目都是属于某一张特定的试卷的;其值应该是 wls_quiz_paper表中的id
	
	this.answer = null;           //正确答案
	this.myAnswer = null;         //我选择的答案
	
	this.cent = 0;                //这个题目的分值
	this.cent_ = 0;               //用户答题后所获得的分值;一般而言;要么是0;要么就等于cent.但简答题则不同
	
	this.type = null;             //题型;字符串;可以是 单选题;多选题;判断题;填空题等
	this.paper = null;             //试卷对象.	
	
	this.markingmethod = 0;       //卷子批改方式=自动批改 或 教师人工批改
	
	this.layout = 'vertical';     //题目选项的排列方式;横向或纵向;默认为纵向;就是一个选项一行
	this.description = '';        //解题思路
	this.title = '';              //题目标题
	
	this.state = '';              //状态 submitted 已提交
	this.path_listen = null;      //听力文件;如果有听力的话
	this.mode = 1;				  // 1  单题模式,无后台交互 ; 2 单题模式,有后台交互 ; 3 试卷模式,无后台交互; 4 试卷模式,有后台交互
	
	//提交了试卷之后,显示 题目做错了 ,这时候用户不服气,觉得错的冤枉,就可以对这道题目做一次评论 '为什么我会错?'
	this.addWhyImWrong = function(){
		$('#w_qs_' + this.id)
				.append("<div class='WhyImWrong'>"
						+ "<table><tr style='color:red;font-size:11px;'>"
						+ "<td width='20%' >"
						+ ":&nbsp;&nbsp;</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='1' name='"
						+ this.id
						+ "' />"
						+ "我的确不会"
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='2' name='"
						+ this.id
						+ "' />"
						+ "我会做得,但是我粗心了"
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='3' name='"
						+ this.id
						+ "' />"
						+ "我不能理解我为什么会错!"
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='4' name='"
						+ this.id + "' />" + "答案错了!我没有错!"
						+ "</td>" + "</tr></table>" + "</div>");
	}
	
	this.submitButton = function(){
		$('#wls_quiz_main').append("<br/><br/><input type='button' onclick='education_question.questionObj.getMyAnswer();education_question.questionObj.showDescription();' style='width:100px;' value='"+top.il8n.submit+"' class='l-button l-button-submit' />");
	}
};

//在试卷模式时,如果做完一道题目,试卷左侧的 题目导航条 会变色标注的
var question_done = function(id){
	if($('#w_q_subQuesNav_' + id).hasClass('w_q_sn_undone')){
		$('#w_q_subQuesNav_' + id).attr('class','w_q_sn_done');
	}
}

//保存评论
var question_saveComment = function(dom) {
	$(".WhyImWrong", $("#w_qs_" + dom.name)).empty();
	$.ajax({
		url : "../../../../wls.php?controller=question&action=saveComment",
		data : {
			id : dom.name,
			value : dom.value
		},
		type : "POST",
		success : function(msg) {
			msg = jQuery.parseJSON(msg);

			var c1 = parseInt(msg.comment_ywrong_1);
			var c2 = parseInt(msg.comment_ywrong_2);
			var c3 = parseInt(msg.comment_ywrong_3);
			var c4 = parseInt(msg.comment_ywrong_4);

			var cc = [];
			cc.push(Math.floor((c1 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c2 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c3 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c4 * 100) / (c1 + c2 + c3 + c4)));
			var str = il8n.normal.statistic + ":";
			for (var i = 0; i < 4; i++) {
				if (i == 0) {
					str += "<span style='background-color:red;color:red' title='"
							+ "我的确不会" + "," + c1 + "'>";
				} else if (i == 1) {
					str += "<span style='background-color:blue;color:blue' title='"
							+ "我粗心了" + "," + c2 + "'>";
				} else if (i == 2) {
					str += "<span style='background-color:gray;color:gray' title='"
							+ "我不知道错在哪" + "," + c3 + "'>";
				} else if (i == 3) {
					str += "<span style='background-color:yellow;color:yellow' title='"
							+ "答案错了,我没错" + "," + c4 + "'>";
				}
				for (ii = 0; ii < cc[i]; ii++) {
					str += "|";
				}
				str += "</span>";
			}
			$(".WhyImWrong", $("#w_qs_" + dom.name)).append(str);
		}
	});
}