var basic_parameter = {
	
	version: "2012X1"
	
	,type: null
	,grid : null
	,ajaxData: null
	
	,EDU_BKZYML :{
		dialog: function(dom,afterAjax){
			var str =  "<table id='birthAddressCodeDialog'>" +
			"<tr><td>"+top.il8n.basic_parameter.lv1+"</td><td>"+top.il8n.basic_parameter.lv2+"</td><td>"+top.il8n.basic_parameter.lv3+"</td></tr>" +
			"<tr>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.EDU_BKZYML.read(event)' size='15' id='select_1'></select></td>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.EDU_BKZYML.read(event)' size='15' id='select_2'></select></td>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.EDU_BKZYML.read(event)' size='15' id='select_3'></select></td>" +
			"<tr><td colspan='2'>&nbsp;&nbsp;<span id='value' value=''></span></td><td><button onclick='basic_parameter.EDU_BKZYML.submit()' class='l-button l-button-submit'>"+top.il8n.ok+"</button></td></tr>"+
			"</tr></table>"
			$(dom).append(str);
			
			this.read();
		}
		,read: function(dom){
			var code = "";
			var selectDom = $('#select_1');
			if (dom != undefined){		
				
				if($.browser.msie){
					var code = $("option:selected",$(dom.srcElement)).attr("value");
				}else{
					var code = $("option:selected",$(dom.target)).attr("value");
				}
				if(code.length==2)
					selectDom = $('#select_2');
				if(code.length==4)
					selectDom = $('#select_3');		
				if(code.length==6)
					return;
				
				$(selectDom).empty();
			}
		
			$.ajax({
				url: myAppServer() + "&class=basic_parameter&function=getEDU_BKZYML"
				,data: {code: code } 
				,type: "POST"
				,dataType: 'json'						
				,success: function(response) {
					for(var i=0;i<response.length;i++){
						$(selectDom).append("<option value='"+response[i].code+"'>"+response[i].value+"</option>");
					}
				}
			});	
		}
		,submit: function(){
			var code = $('#select_3 option:selected').attr("value");
			var str = $('#select_3 option:selected').html();
			
			if(top == self){
				alert(code+" "+str);
			}else{				
				parent.$.ligerui.get( getParameter("id", window.location.toString()) ).setValue(str);
				parent.$("#"+ getParameter("id", window.location.toString() +"_code") ).attr("value",code);
				parent.$.ligerui.get("EDU_BKZYML").close();
				
			}
		}
	} 

	,JB_GDXXHKYJG: {
		dialog: function(dom,afterAjax){
			var str =  "<div ><select style='width:178px;float:left' id='select' onchange='basic_parameter.JB_GDXXHKYJG.read(event)'></select></div><div id='content'></div>";
			$(dom).append(str);
			
			$.ajax({
				url: myAppServer() + "&class=basic_parameter&function=getGB2260"
				,data: {code: "" } 
				,type: "POST"
				,dataType: 'json'						
				,success: function(response) {
					for(var i=0;i<response.length;i++){
						$("#select").append("<option value='"+response[i].code+"'>"+response[i].value+"</option>");
					}
				}
			});	
		}
		,read: function(dom){
			$('#content').empty();
			var code = $("#select option:selected").attr("value");
			
			$.ajax({
				url: myAppServer() + "&class=basic_parameter&function=getJBGDXXHKYJG"
				,data: {code: code } 
				,type: "POST"
				,dataType: 'json'						
				,success: function(response) {
					for(var i=0;i<response.length;i++){
						$("#content").append("<div onclick='basic_parameter.JB_GDXXHKYJG.click(event)' class='l-button' style='width:178px;float:left' value='"+response[i].code+"' >"+response[i].value+"</div>");
					}
				}
			});	
		}
		,click: function(event){
			var code;
			var str ;
			if($.browser.msie){
				code = $(event.srcElement).attr("value");
				str = $(event.srcElement).html();
			}else{
				code = $(event.target).attr("value");
				str = $(event.target).html();
			}
			
			if(top == self){
				alert(code+" "+str);
			}else{
				parent.$.ligerui.get( getParameter("id", window.location.toString()) ).setValue(str);
				parent.$("#"+ getParameter("id", window.location.toString() +"_code") ).attr("value",code);
			}
		}
	}
	
	,GB2260: {
		dialog: function(dom,afterAjax){
			var str =  "<table >" +
			"<tr><td>"+top.il8n.basic_parameter.lv1+"</td><td>"+top.il8n.basic_parameter.lv2+"</td><td>"+top.il8n.basic_parameter.lv3+"</td></tr>" +
			"<tr>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.GB2260.read(event)' size='15' id='select_1'></select></td>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.GB2260.read(event)' size='15' id='select_2'></select></td>" +
			"	<td valign='top'><select style='width:110px;' onchange='basic_parameter.GB2260.read(event)' size='15' id='select_3'></select></td>" +
			"<tr><td colspan='2'>&nbsp;&nbsp;<span id='value' value=''></span></td><td><button onclick='basic_parameter.GB2260.submit()' class='l-button l-button-submit'>"+top.il8n.ok+"</button></td></tr>"+
			"</tr></table>"
			$(dom).append(str);
			
			this.read();
		}
		,read: function(dom){
			var code = "";
			var selectDom = $('#select_1');
			if (dom != undefined){		
				
				if($.browser.msie){
					var code = $("option:selected",$(dom.srcElement)).attr("value");
				}else{
					var code = $("option:selected",$(dom.target)).attr("value");
				}
				if(code.length==2)
					selectDom = $('#select_2');
				if(code.length==4)
					selectDom = $('#select_3');		
				if(code.length==6)
					return;
				
				$(selectDom).empty();
			}
		
			$.ajax({
				url: myAppServer() + "&class=basic_parameter&function=getGB2260"
				,data: {code: code } 
				,type: "POST"
				,dataType: 'json'						
				,success: function(response) {
					for(var i=0;i<response.length;i++){
						$(selectDom).append("<option value='"+response[i].code+"'>"+response[i].value+"</option>");
					}
				}
			});	
		}
		,submit: function(event){
			var code = $('#select_3 option:selected').attr("value");
			var str = $('#select_1 option:selected').html()+"."+$('#select_2 option:selected').html()+"."+$('#select_3 option:selected').html();
			if(typeof(code)=='undefined'){
				code = $('#select_2 option:selected').attr("value");
				str = $('#select_1 option:selected').html()+"."+$('#select_2 option:selected').html();
			}
			
			if(top == self){
				alert(code+" "+str);
			}else{
				parent.$.ligerui.get('GB2260').setValue(str);
				parent.$("#GB2260_code").attr("value",code);
			}
		}
	}
	
	,photoUpload: function(){
		var uploader = new qq.FileUploader({
			element: document.body
			,action: '../php/myApp.php?class=basic_parameter&function=photoUpload'
			,allowedExtensions: ["jpg","gif","png","bmp","jpeg"]
			,params: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))}
			,debug: true
			,onComplete: function(id, fileName, responseJSON){
				//console.debug(id+" "+fileName);
				//console.debug(responseJSON);
				if(top == self){
					alert(responseJSON.path);
				}else{
					parent.$("#"+ getParameter("id", window.location.toString() ) ).attr("value",responseJSON.path);
				}
				$('#img').attr('src',responseJSON.path);
			}
        });    
		
		$(document.body).append("<hr/>");
		$(document.body).append("<img style='width:300px;height:200px;' id='img' src='../file/nophoto.jpg' />");		
		
		var path = getParameter("path", window.location.toString() );
		if(path!=null && path!=""){
			$('#img').attr('src',path);
		}
	}
	
	,mp3Upload: function(){
		var uploader = new qq.FileUploader({
			element: document.body
			,action: '../php/myApp.php?class=basic_parameter&function=mp3Upload'
			,allowedExtensions: ["mp3"]
			,params: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))}
			,debug: true
			,onComplete: function(id, fileName, responseJSON){
				//console.debug(id+" "+fileName);
				//console.debug(responseJSON);
				if(top == self){
					alert(responseJSON.path);
				}else{
					parent.$("#"+ getParameter("id", window.location.toString() ) ).attr("value",responseJSON.path);
				}
				$(document.body).append('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="250" height="20" '
						   + 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab"> '
						   + '<param name="movie" value="../libs/singlemp3player.swf?file=../libs/'+responseJSON.path+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" /> '
						   + '<param name="wmode" value="transparent" /> '
						   + '<embed wmode="transparent" width="250" height="20" src="../libs/singlemp3player.swf?file=../libs/'+responseJSON.path+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" '
						   + 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /> '
						   + '</object>');	
			}
        });    
		
		$(document.body).append("<hr/>");
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
					for(var i=0; i<top.il8n.basic_parameter__types.length; i++){
						if(top.il8n.basic_parameter__types[i].code == a.type){
							return top.il8n.basic_parameter__types[i].value;
						}
					}
				} },
				{ display: top.il8n.basic_parameter.code, name: 'code' ,width: 50 ,isSort : false},
				{ display: top.il8n.basic_parameter.icon, name: 'icon' ,align: 'left', width: 40, render : function(a,b){
					if(a.icon!=""&&a.icon!=null){
						return "<img src='"+a.icon+"' height='16px' wiedth='16px' />";
					}
				}},
				{ display: top.il8n.basic_parameter.status, name: 'status', width: 55, isSort : false , render: function(a,b){
					if(a.status=="0"){
						return top.il8n.disabled;
					}else if(a.status=="1"){
						return top.il8n.enabled;
					}else{
						return a.status;
					}
				} },
				{ display: top.il8n.basic_parameter.count_users, name: 'count_users'},
				{ display: top.il8n.basic_parameter.remark, name: 'remark'}
			], rownumbers:true,height:'100%',usePager:false,
			parms : {username : top.basic_user.username,
				session : MD5( top.basic_user.session +((new Date()).getHours()))},
			url : "../php/myApp.php?class=basic_parameter&function=getGrid",
			method  : "POST",
			id : "basic_parameter__grid",
			toolbar: { items: [] }
		};
		
		var group = [];
		for(var i=0;i<top.basic_user.permission.length;i++){
			if(top.basic_user.permission[i].code=='12'){
				group = top.basic_user.permission[i].children;
				for(var j=0;j<group.length;j++){
					if(group[j].code=='1201'){
						group = group[j].children;
					}
				}				
			}
		}
		for(var i=0;i<group.length;i++){
			if(group[i].code=='120101'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						basic_parameter.upload();
					}
				});
			}else if(group[i].code=='120102'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						basic_parameter.download();
					}
				});
			}else if(group[i].code=='120103'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						basic_parameter.delet();
					}
				});
			}else if(group[i].code=='120104'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						basic_parameter.modify();
					}
				});
			}else if(group[i].code=='120105'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						top.$.ligerDialog.open({ 
							url: 'basic_parameter__insert.html?random='+Math.random(), height: 500,width: 400
							,title: top.il8n.add
							,isHidden: false
						});
					}
				});
			}else if(group[i].code=='120106'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: group[i].name , img:group[i].icon , click : function(){
						var selected;
						if(basic_parameter.grid.options.checkbox){
							selected = basic_parameter.grid.getSelecteds();
							if(selected.length!=1){alert(il8n.selectOneItemOnGrid);return;}
							selected = selected[0];
						}else{
							selected = basic_parameter.grid.getSelected();
							if(selected==null){
								alert(il8n.GRID.noSelect);return;
							}
						}						
						top.$.ligerDialog.open({ 
							url: 'basic_parameter_2_permission__tree.html?code='+selected.code+'&random='+Math.random(), height: 500,width: 400
							,title: top.il8n.modify
							,isHidden: false
						});
					}
				});
			}
		}
		
		this.grid = $(document.body).ligerGrid(config);
	}

	,insert: function(
			dom, //如果为空,则返回一个 ligerForm 的参数配置对象
			afterInsert //如果数据插入后,AJAX返回成功,需要执行一个回调函数
			){
		
		var config = {
			id: 'basic_parameter__insert',
			fields: [
				 { display: top.il8n.title, name: "basic_parameter__name",  type: "text",  validate: { required:true, minlength:3, maxlength:10} }
				,{ display: top.il8n.type, name: "basic_parameter__type", type: "select" , options :{data : top.il8n.basic_parameter__types, valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.code, name: "basic_parameter__code",  type: "text", validate: {required:true, digits:true, minlength:2, maxlength:10 } }
				,{ display: top.il8n.icon, name: "basic_parameter__icon",  type: "text" }
				,{ display: top.il8n.status, name: "basic_parameter__status", type: "select" , options :{data : [{code:"1",value:top.il8n.enabled},{code:"0",value:top.il8n.disabled}], valueField : "code" , textField: "value", slide: false }, validate: {required:true} }
				,{ display: top.il8n.remark, name: "basic_parameter__remark",  type: "text" }
			]
		};
		
		//主要用在用户信息修改,在其他页面潜入 用户添加功能 的时候,需要使用
		if (dom == undefined){
			return config;
		}else{
			var form = $(dom).ligerForm(config);
			
			$(dom).append('<br/><br/><br/><br/><div id="buttons"><input type="submit" value="'+top.il8n.submit+'" id="basic_parameter__submit" class="l-button l-button-submit" /></div>' );
			
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
					$("#basic_parameter__submit").attr("value",top.il8n.waitting);
					
					basic_user.type = $.ligerui.get('basic_parameter__type').getValue();
					$.ajax({
						url: myAppServer() + "&class=basic_parameter&function=insert",
						data: {
							json:$.ligerui.toJSON({
								 name: $.ligerui.get('basic_parameter__name').getValue()
								,type: $.ligerui.get('basic_parameter__type').getValue()
								,code: $.ligerui.get('basic_parameter__code').getValue()
								,icon: $.ligerui.get('basic_parameter__icon').getValue()
								,status: $.ligerui.get('basic_parameter__status').getValue()
								,remark: $.ligerui.get('basic_parameter__remark').getValue()

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
								basic_parameter.ajaxState = false;
								basic_parameter.type = $.ligerui.get('basic_parameter__type').getValue();
								$("#basic_parameter__submit").val(top.il8n.submit);	
								
								//如果参数中,有 回调函数,则执行
								if ( typeof(afterInsert) == "string" ){
									eval(afterInsert);
								}
							
							//服务端添加失败
							}else if(response.state==0){
								basic_parameter.ajaxState = false;
								$("#basic_parameter__submit").val(top.il8n.submit);									
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
	
	,ajaxState: false 
	

	,afterInsert: function(){
		$('#buttons').empty();
		
		//组织机构类型的编码,可以额外补充组织机构信息
		if(this.type=='2'){
			$('#buttons').append('<input type="submit" style="width:100px;" value="'+top.il8n.basic_parameter.addparameterInfo+'" onclick="basic_parameter.addExtend()" class="l-button l-button-submit" />');
		}
	}
	
	//如果在添加用户组的时候,选择的类型是 组织机构 ,就意味着同时也添加了一条 组织机构 记录
	//还需要再补充组织机构的信息
	,parameterId: null
	,addExtend: function(){
		top.$.ligerDialog.open({ 
			url: 'basic_parameter__update.html?id='+basic_user.insertResponse.person+'&random='+Math.random(), height: 540,width: 700
			,title: top.il8n.basic_user.addPersonInfo
			,isHidden: false
		});
	}
	
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = basic_parameter.grid.getSelecteds();
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
				url: myAppServer() + "&class=basic_parameter&function=delete",
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
						basic_parameter.grid.loadData();
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
		if($.ligerui.get("basic_parameter__grid_upload_d")){
			dialog = $.ligerui.get("basic_parameter__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='basic_parameter__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('basic_parameter__grid_file'),
				action: '../php/myApp.php?class=basic_parameter&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/basic_parameter__add.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					basic_parameter.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "basic_parameter__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_parameter__grid_file"),
				modal : true
			});
		}
	},
	download: function(){
		var dialog;
		if($.ligerui.get("basic_parameter__grid_download_d")){
			dialog = $.ligerui.get("basic_parameter__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='basic_parameter__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "basic_parameter__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_parameter__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=basic_parameter&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					$.ligerDialog.error(response.msg);
				}else if(response.state==1){
					$("#basic_parameter__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a>");
				}
			},
			error : function(){
				manager.close();
				$.ligerDialog.error(top.il8n.AJAX.disConnect);
			}
		});		
	}
};