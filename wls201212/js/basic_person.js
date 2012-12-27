var basic_person = {
		
	foo: 1
	,id: null
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=basic_person&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				basic_person.config = response;
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
	 * 添加一个用户
	 * 前端以表单的形式向后台提交数据,服务端AJAX解析入库,
	 * 服务端还会反馈一些数据,比如 用户编号 等
	 * */
	,insert: function(
			dom //如果为空,则返回一个 ligerForm 的参数配置对象
			,afterInsert //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		var config = {
			id: 'basic_person__addForm',
			fields: [
				{ display: top.il8n.basic_person.name, name: "basic_person__name", type: "text",  validate: { required:true, minlength:2, maxlength:10} }
				,{ display: top.il8n.basic_person.gender, name: "basic_person__gender", type: "select", options :{data : basic_person.config.GB2261_1, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.basic_person.birthday, name: "basic_person__birthday", type: "date" , validate : {required:true} }
				,{ display: top.il8n.basic_person.nation, name: "basic_person__nation", type: "select", options :{data : basic_person.config.GB3304, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.basic_person.cardType, name: "basic_person__cardType", type: "select" , options :{data : basic_person.config.cardType, valueField : "code" , textField: "value", slide: false } }
				,{ display: top.il8n.basic_person.idcard, name: "basic_person__idcard", type: "text",  validate: {minlength: 5} }
				
				,{ display: top.il8n.basic_person.nationality, name: "basic_person__nationality", type: "text" }
				,{ display: top.il8n.basic_person.height, name: "basic_person__height", type: "text",  validate: {digits: true, min: 50, max: 300} ,newline: false }
				
				,{ display: top.il8n.basic_person.address_birth, name: "GB2260", type: "text" }
				,{ display: top.il8n.basic_person.ismarried, name: "basic_person__ismarried", type: "select", options :{data : basic_person.config.GB2261_2, valueField : "code" , textField: "value" },  newline: false }
				
				,{ display: top.il8n.basic_person.degree_school, name: "JB_GDXXHKYJG", type: "text" }
				,{ display: top.il8n.basic_person.degree, name: "basic_person__degree", type: "select" , options :{data : basic_person.config.GB4568, valueField : "code" , textField: "value"}, newline: false  }
				
				,{ display: top.il8n.basic_person.email, name: "basic_person__email", type: "text" }
				,{ display: top.il8n.basic_person.politically, name: "basic_person__politically", type: "select" , options :{data : basic_person.config.GB4762, valueField : "code" , textField: "value"}, newline: false  }
								
				,{ display: top.il8n.basic_person.cellphone, name: "basic_person__cellphone", type: "text" }
				,{ display: top.il8n.basic_person.qq, name: "basic_person__qq", type: "text",  validate: {digits: true } ,newline: false }
								
				,{ display: top.il8n.basic_person.address, name: "basic_person__address", type: "text", width: 470  }
				,{ display: top.il8n.remark, name: "basic_person__remark", type: "text", width: 470  }				
				
				,{ name: "GB2260_code", type: "hidden" }
				,{ name: "basic_person__address_code", type: "hidden"}		
				,{ name: "JB_GDXXHKYJG_code", type: "hidden"}					
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			$(dom).ligerForm(config);
			$(dom).append('<br/><br/><br/><br/><input type="submit" value="'+top.il8n.submit+'" id="basic_person__submit" class="l-button l-button-submit" />' );
			
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
					if(basic_person.insertAjaxState)return;
					basic_person.insertAjaxState = true;
					$("#basic_person__submit").attr("value",top.il8n.waitting);
					
					$.ajax({
						url: myAppServer() + "&class=basic_person&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 name: $.ligerui.get('basic_person__name').getValue()
								,gender: $.ligerui.get('basic_person__gender').getValue()
								,birthday: $.ligerui.get('basic_person__birthday').getValue()
								,nation: $.ligerui.get('basic_person__nation').getValue()
								,cardType: $.ligerui.get('basic_person__cardType').getValue()
								,idcard: $.ligerui.get('basic_person__idcard').getValue()
								
								,nationality: $.ligerui.get('basic_person__nationality').getValue()
								,height: $.ligerui.get('basic_person__height').getValue()
								
								,address_birth: $.ligerui.get('GB2260').getValue()
								,address_birth_code: $('#GB2260_code').val()
								,ismarried: $.ligerui.get('basic_person__ismarried').getValue()
								
								,degree_school: $.ligerui.get('JB_GDXXHKYJG').getValue()
								,degree_school_code: $.ligerui.get('JB_GDXXHKYJG_code').getValue()
								,degree: $.ligerui.get('basic_person__degree').getValue()
								
								,email: $.ligerui.get('basic_person__email').getValue()
								,politically: $.ligerui.get('basic_person__politically').getValue()								
								
								,cellphone: $.ligerui.get('basic_person__cellphone').getValue()
								,qq: $.ligerui.get('basic_person__qq').getValue()
								
								,address: $.ligerui.get('basic_person__address').getValue()
								,address_code: $('#basic_person__address_code').val()
								
								,remark: $.ligerui.get('basic_person__remark').getValue()
								
								,photo: $('#basic_person__photo').attr("src")
							}),
							
							//服务端权限验证所需
							personname: top.basic_person.personname,
							session: MD5( top.basic_person.session +((new Date()).getHours()))
						},
						type: "POST",
						dataType: 'json',						
						success: function(response) {		
							//服务端添加成功,修改 AJAX 通信状态,修改按钮的文字信息,读取反馈信息
							if(response.state==1){
								basic_person.insertAjaxState = false;
								$("#basic_person__submit").val(top.il8n.submit);	

								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								basic_person.insertAjaxState = false;
								$("#basic_person__submit").val(top.il8n.submit);									
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

	,ajaxData: {}
	,update: function(dom,afterAjax){
		var config = this.insert();
		this.id = getParameter("id", window.location.toString() );
		$(dom).ligerForm(config);
		$(dom).append('<br/><br/><br/><br/><input type="submit" value="'+top.il8n.modify+'" id="basic_person__submit" class="l-button l-button-submit" />' );
		
		//从服务端读取信息,填充表单内容
		$.ajax({
			url: myAppServer() + "&class=basic_person&function=view"
			,data: {
				id: basic_person.id
				
				//服务端权限验证所需
				,personname: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))
			}
			,type: "POST"
			,dataType: 'json'						
			,success: function(response) {	
			    basic_person.ajaxData = response;
				$.ligerui.get('basic_person__name').setValue(response.name);
				$.ligerui.get('basic_person__gender').setValue(response.gender);
				$.ligerui.get('basic_person__birthday').setValue(response.birthday);				 
				$.ligerui.get('basic_person__nation').setValue(response.nation);
				$.ligerui.get('basic_person__cardType').setValue(response.cardType);
				$.ligerui.get('basic_person__idcard').setValue(response.idcard);
				 
				$.ligerui.get('basic_person__nationality').setValue(response.nationality);
				$.ligerui.get('basic_person__height').setValue(response.height);
				 
				$.ligerui.get('GB2260').setValue(response.address_birth);
				$('#GB2260_code').attr("value",response.address_birth_code);
				$.ligerui.get('basic_person__ismarried').setValue(response.ismarried);
				 
				$.ligerui.get('JB_GDXXHKYJG').setValue(response.degree_school);
				$('#JB_GDXXHKYJG_code').attr("value",response.degree_school_code);
				$.ligerui.get('basic_person__degree').setValue(response.degree);				 
				
				$.ligerui.get('basic_person__email').setValue(response.email);
				$.ligerui.get('basic_person__politically').setValue(response.politically);		
				 
				$.ligerui.get('basic_person__cellphone').setValue(response.cellphone);
				$.ligerui.get('basic_person__qq').setValue(response.qq);		
				 
				$.ligerui.get('basic_person__address').setValue(response.address);
				$('#basic_person__address_code').attr("value",response.address_code);
				 
				$.ligerui.get('basic_person__remark').setValue(response.remark);	
				 
				$('#basic_person__photo').attr("src",response.photo);
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
				if(basic_person.insertAjaxState)return;
				basic_person.insertAjaxState = true;
				$("#basic_person__submit").attr("value",top.il8n.waitting);
				
				var data = {
				    id: basic_person.id	
				};
				if(basic_person.ajaxData.name != $.ligerui.get('basic_person__name').getValue() )
					data.name = $.ligerui.get('basic_person__name').getValue();
				if(basic_person.ajaxData.gender != $.ligerui.get('basic_person__gender').getValue() )
					data.gender = $.ligerui.get('basic_person__gender').getValue();	
				if(basic_person.ajaxData.birthday != $('#basic_person__birthday').val() )
					data.birthday = $('#basic_person__birthday').val();	
				if(basic_person.ajaxData.nation != $.ligerui.get('basic_person__nation').getValue() )
					data.nation = $.ligerui.get('basic_person__nation').getValue();	
				if(basic_person.ajaxData.cardType != $.ligerui.get('basic_person__cardType').getValue() )
					data.cardType = $.ligerui.get('basic_person__cardType').getValue();	
				if(basic_person.ajaxData.idcard != $.ligerui.get('basic_person__idcard').getValue() )
					data.idcard = $.ligerui.get('basic_person__idcard').getValue();	
				
				if(basic_person.ajaxData.nationality != $.ligerui.get('basic_person__nationality').getValue() )
					data.nationality = $.ligerui.get('basic_person__nationality').getValue();	
				if(basic_person.ajaxData.height != $.ligerui.get('basic_person__height').getValue() )
					data.height = $.ligerui.get('basic_person__height').getValue();		
				
				if(basic_person.ajaxData.address_birth != $.ligerui.get('GB2260').getValue() ){
					data.address_birth = $.ligerui.get('GB2260').getValue();
					data.address_birth_code = $('#GB2260_code').attr("value");
				}
				if(basic_person.ajaxData.degree != $.ligerui.get('basic_person__degree').getValue() )
					data.degree = $.ligerui.get('basic_person__degree').getValue();		
								
				if(basic_person.ajaxData.degree_school != $.ligerui.get('JB_GDXXHKYJG').getValue() ){
					data.degree_school = $.ligerui.get('JB_GDXXHKYJG').getValue();
					data.degree_school_code = $('#JB_GDXXHKYJG_code').attr("value");
				}
				
				if(basic_person.ajaxData.ismarried != $.ligerui.get('basic_person__ismarried').getValue() )
					data.ismarried = $.ligerui.get('basic_person__ismarried').getValue();	
				
				if(basic_person.ajaxData.email != $.ligerui.get('basic_person__email').getValue() )
					data.email = $.ligerui.get('basic_person__email').getValue();	
				if(basic_person.ajaxData.politically != $.ligerui.get('basic_person__politically').getValue() )
					data.politically = $.ligerui.get('basic_person__politically').getValue();	
				
				if(basic_person.ajaxData.cellphone != $.ligerui.get('basic_person__cellphone').getValue() )
					data.cellphone = $.ligerui.get('basic_person__cellphone').getValue();	
				if(basic_person.ajaxData.qq != $.ligerui.get('basic_person__qq').getValue() )
					data.qq = $.ligerui.get('basic_person__qq').getValue();	
				
				if(basic_person.ajaxData.address != $.ligerui.get('basic_person__address').getValue() ){
					data.address = $.ligerui.get('basic_person__address').getValue();	
					data.address_code = $('#basic_person__address_code').val();
				}
				
				if(basic_person.ajaxData.remark != $.ligerui.get('basic_person__remark').getValue() )
					data.remark = $.ligerui.get('basic_person__remark').getValue();	

				if(basic_person.ajaxData.photo != $('#basic_person__photo').attr("src"))
					data.photo = $('#basic_person__photo').attr("src");	
				
				var toUpdateColumn = 0;
				for (var obj in data){
					toUpdateColumn ++;
				}
				if(toUpdateColumn==1){
					alert(top.il8n.nothingModified);
					return;
				}
				$.ajax({
					url: myAppServer() + "&class=basic_person&function=update"
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
							basic_person.insertAjaxState = false;
							$("#basic_person__submit").val(top.il8n.modify);	
							alert('done');
	
							//如果参数中,有 回调函数,则执行
							if ( typeof(afterInsert) == "string" ){
								eval(afterInsert);
							}
						
						//服务端添加失败
						}else if(response.state==0){
							basic_person.insertAjaxState = false;
							$("#basic_person__submit").val(top.il8n.submit);									
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
	
	//AJAX 通信状态,如果为TRUE,则表示服务端还在通信中	
	,insertAjaxState: false 
	
	,afterFormCreated: function(formId){
		var dom = $("li",$("#GB2260").parent().parent().parent())[2];
		$(dom).append('<a onclick="basic_person.birthAddressCodeDialog()" href="#" title="'+top.il8n.basic_person.findMyAddressCode+'"><img src="../file/icon16x16/icon-16-redirect.png"></a>');
		var dom = $("li",$("#basic_person__address").parent().parent().parent())[2];
		$(dom).append('<a onclick="basic_person.addressCodeDialog()" href="#" title="'+top.il8n.basic_person.findMyAddressCode+'"><img src="../file/icon16x16/icon-16-redirect.png"></a>');
		var dom = $("li",$("#JB_GDXXHKYJG").parent().parent().parent())[2];
		$(dom).append('<a onclick="basic_person.schoolCodeDialog()" href="#" title="'+top.il8n.basic_person.findMyAddressCode+'"><img src="../file/icon16x16/icon-16-redirect.png"></a>');
		$('#form').append("<div style='position: absolute;left: 400px;top: 15px;width: 125px;height:145px;' onclick=\"basic_person.photoDialog()\" >" +
				"<img style='width:120px;height:140px' id='basic_person__photo' src='../file/nophoto.jpg' />" +
				"</div>");
	}
	

	,birthAddressCodeDialog: function(par){
		top.$.ligerDialog.open({ 
			url: 'basic_parameter__GB2260.html', height: 500,width: 400
			,title: top.il8n.basic_person.address_birth
			,isHidden: false
		});
	}
	
	,addressCodeDialog: function(){
		
	}
	
	,schoolCodeDialog: function(){
		$.ligerDialog.open({ 
			url: 'basic_parameter__JB-GDXXHKYJG.html?id=JB_GDXXHKYJG', height: 500,width: 400
			,title: top.il8n.basic_person.degree_school
			,isHidden: false
		});
	}
	
	/**
	 * 个人照片上传
	 * 
	 * 前端:
	 *  使用一个开源的 qq.FileUploader 实现无 form 上传文件
	 * 
	 * 服务端:
	 *  系统在存储照片文件的时候,是存储在 文件夹结构 中的,
	 * 没有存储在数据库中
	 * */
	,photoDialog: function(){
		var dialog;
		if($.ligerui.get("basic_person__photo_upload_d")){
			dialog = $.ligerui.get("basic_person__photo_upload_d");
			dialog.show();
		}else{
	
			$(document.body).append( $("<div id='basic_person__insert_file'></div>"));		
			var uploader = new qq.FileUploader({
				element: document.getElementById('basic_person__insert_file'),
				action: '../php/myApp.php?class=basic_person&function=photoUpload',
				allowedExtensions: ["jpg","JPG","png","PNG","gif","GIF"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					$("#basic_person__photo").attr("src",responseJSON.path);
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "basic_person__photo_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_person__insert_file"),
				modal : true
			});
		}
	}
};