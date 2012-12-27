var basic_permission = {
	
	version: "2012X1"		
		
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=basic_permission&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				basic_permission.config = response;
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
		
	,grid : null
	,initGrid : function(){
		var config = {
			columns: [
				{ display: top.il8n.title, name: 'name', align: 'left', width: 170, minWidth: 60, render: function(a,b){
					var str = a.name;
					for(var i=0; i<a.code.length-2; i++){
						str = "-"+str;
					}
					return str;
				} },
				{ display: top.il8n.type, name: 'type',width: 55, isSort: false, render: function(a,b){
					for(var i=0; i<basic_permission.config.type.length; i++){
						if( basic_permission.config.type[i].code == a.type){
							return basic_permission.config.type[i].value;
						}
					}				
				} },
				{ display: top.il8n.basic_permission.code, name: 'code' ,width: 50,align:'left' ,isSort : false},
				{ display: top.il8n.basic_permission.icon, name: 'icon' ,align: 'left', width: 90, render : function(a,b){
					if(a.type=="1"||a.type=="2"||a.type=="3"){
						return "<img src='"+a.icon+"' height='16px' wiedth='16px' />";
					}else{
						return "&nbsp;";
					}
				}},
				{ display: top.il8n.basic_permission.path, name: 'path',  isSort : false }
			], usePager:false,height:'100%',
			parms : {username : top.basic_user.username,session : MD5( top.basic_user.session +((new Date()).getHours()))},
			url : "../php/myApp.php?class=basic_permission&function=getGrid",
			method  : "POST",
			id : "basic_permission__grid",
			toolbar: { items: [] }
		};
		
		var permission = [];
		for(var i=0;i<top.basic_user.permission.length;i++){
			if(top.basic_user.permission[i].code=='12'){
				permission = top.basic_user.permission[i].children;
				for(var j=0;j<permission.length;j++){
					if(permission[j].code=='1203'){
						permission = permission[j].children;
					}
				}				
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='120311'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_permission.upload();
					}
				});
			}else if(permission[i].code=='120312'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_permission.download();
					}
				});
			}else if(permission[i].code=='120321'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_permission.insert();
					}
				});
			}else if(permission[i].code=='120322'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_permission.delet();
					}
				});
			}else if(permission[i].code=='120323'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						basic_permission.update();
					}
				});
			}
		}

		basic_permission.grid = $(document.body).ligerGrid(config);
	},
	insert: function(){		
		var formD;
		if($.ligerui.get("basic_permission__grid_add_d")){
			formD = $.ligerui.get("basic_permission__grid_add_d");
			formD.show();
		}else{
			$.metadata.setType("attr", "validate");
			var form = $("<form id='basic_permission__grid_add'></form>");
			$(form).ligerForm({
				inputWidth: 170, labelWidth: 90, space: 40,
				fields: [
					{ display: top.il8n.title , name: "name", newline: false, type: "text" ,validate : {required:true,minlength:2,maxlength:10} },
					{ display: top.il8n.type , name: "type", newline: true, type: "select", comboboxName: "type_select", validate : {required:true},
						options: { data : [
						                   {text: top.il8n.basic_permission.node, id:1 },
						                   {text: top.il8n.basic_permission.webpage, id:2 }
						                   ], slide:false } 
					},
					{ display: top.il8n.basic_permission.code , name: "code", newline: true, type: "number", validate: {digits:true,min:50,required:true,minlength:2,maxlength:10} },
					{ display: top.il8n.basic_permission.rank , name: "rank", newline: true, type: "number", validate: {digits:true} },
					{ display: top.il8n.basic_permission.icon , name: "icon", newline: true, type: "text", validate: {accept:"gif"} },
					{ display: top.il8n.basic_permission.path , name: "path", newline: true, type: "text" },
					{ display: top.il8n.basic_permission.remark , name: "remark", newline: true, type: "text", validate: {maxlength:100} }
				]
			}); 
			$.ligerDialog.open({
				id : "basic_permission__grid_add_d",
				width : 350,
				height : 280,
				content : form,
				buttons : [
					{text:top.il8n.add,onclick:function(){
						$("#basic_permission__grid_add").submit();
					}}
				]
			});
			
			var v = $("#basic_permission__grid_add").validate({
				debug: true,
				errorPlacement: function (lable, element) {
					if (element.hasClass("l-textarea")) {
					element.addClass("l-textarea-invalid");
					}
					else if (element.hasClass("l-text-field")) {
					element.parent().addClass("l-text-invalid");
					} 
				},
				success: function (lable) {
					var element = $("[ligeruiid="+$(lable).attr('for')+"]",$("form"));
					if (element.hasClass("l-textarea")) {
						element.removeClass("l-textarea-invalid");
					} else if (element.hasClass("l-text-field")) {
						element.parent().removeClass("l-text-invalid");
					}
				},
				submitHandler: function () {
					var manager = $.ligerDialog.waitting(top.il8n.AJAX.waitting);
					$.ajax({
						url : "../php/myApp.php?class=basic_permission&function=add",
						type : "POST",
						dataType: 'json',
						data: {username: top.basic_user.username,
							session: MD5( top.basic_user.session +((new Date()).getHours())),
							json:$.ligerui.toJSON({
								name: $.ligerui.get("name").getValue(),
								type: $.ligerui.get("type_select").getValue(),
								code: $.ligerui.get("code").getValue(),
								icon: $.ligerui.get("icon").getValue(),
								path: $.ligerui.get("path").getValue(),
								remark: $.ligerui.get("remark").getValue()
							}) },
						success : function(response) {
							manager.close();
							if(response.state==0){
								$.ligerDialog.error(response.msg);
							}else if(response.state==1){
								$.ligerDialog.success(top.il8n.AJAX.done);
								basic_permission.grid.loadData();
							}
						},
						error : function(){
							manager.close();
							$.ligerDialog.error(top.il8n.AJAX.disConnect);
						}
					});
				}
			});
		}
	},
	delet: function(){
		var selected = basic_permission.grid.getSelected();
		if(selected==null){
			$.ligerDialog.error(il8n.GRID.noSelect);return;
		}
		var manager = $.ligerDialog.waitting(top.il8n.AJAX.waitting);
		$.ajax({
			url : "../php/myApp.php?class=basic_permission&function=delete",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours())),
				code: selected.code},
			success : function(response) {
				manager.close();
				if(response.state==0){
					$.ligerDialog.error(response.msg);
				}else if(response.state==1){
					$.ligerDialog.success(top.il8n.AJAX.done);
					basic_permission.grid.loadData();
				}
			},
			error : function(){
				manager.close();
				$.ligerDialog.error(top.il8n.AJAX.disConnect);
			}
		});		
	}
	
	,update: function(){
		var selected = basic_permission.grid.getSelected();
		if(selected==null){
			$.ligerDialog.error(il8n.GRID.noSelect);return;
		}
		
		var formD;
		if($.ligerui.get("basic_permission__grid_modify_d")){
			formD = $.ligerui.get("basic_permission__grid_modify_d");
			formD.show();
		}else{
			$.metadata.setType("attr", "validate");
			var form = $("<form id='basic_permission__grid_modify'></form>");
			$(form).ligerForm({
				inputWidth: 170, labelWidth: 90, space: 40,
				fields: [
					{ display: top.il8n.title , name: "m_name", newline: false, type: "text", validate : {required:true,minlength:2,maxlength:10} },
					{ display: top.il8n.basic_permission.rank , name: "m_rank", newline: true, type: "number", validate: {digits:true} },
					{ display: top.il8n.basic_permission.icon , name: "m_icon", newline: true, type: "text", validate: {accept:["gif","png"]} },
					{ display: top.il8n.basic_permission.path , name: "m_path", newline: true, type: "text" },
					{ display: top.il8n.basic_permission.remark , name: "m_remark", newline: true, type: "text", validate: {maxlength:100} }
				]
			}); 
			$.ligerDialog.open({
				id : "basic_permission__grid_modify_d",
				width : 350,
				height : 200,
				content : form,
				buttons : [
					{text:top.il8n.modify,onclick:function(){
						$("#basic_permission__grid_modify").submit();
					}}
				]
			});
			
			var v = $("#basic_permission__grid_modify").validate({
				debug: true,
				errorPlacement: function (lable, element) {
					if (element.hasClass("l-textarea")) {
					element.addClass("l-textarea-invalid");
					}
					else if (element.hasClass("l-text-field")) {
					element.parent().addClass("l-text-invalid");
					} 
				},
				success: function (lable) {
					var element = $("[ligeruiid="+$(lable).attr('for')+"]",$("form"));
					if (element.hasClass("l-textarea")) {
						element.removeClass("l-textarea-invalid");
					} else if (element.hasClass("l-text-field")) {
						element.parent().removeClass("l-text-invalid");
					}
				},
				submitHandler: function () {
					var manager = $.ligerDialog.waitting(top.il8n.AJAX.waitting);
					$.ajax({
						url : "../php/myApp.php?class=basic_permission&function=modify",
						type : "POST",
						dataType: 'json',
						data: {username: top.basic_user.username,
							session: MD5( top.basic_user.session +((new Date()).getHours())),
							json:$.ligerui.toJSON({
								name: $.ligerui.get("m_name").getValue(),
								rank: $.ligerui.get("m_rank").getValue(),
								icon: $.ligerui.get("m_icon").getValue(),
								path: $.ligerui.get("m_path").getValue(),
								remark: $.ligerui.get("m_remark").getValue(),
								code: selected.code
							}) },
						success : function(response) {
							manager.close();
							if(response.state==0){
								$.ligerDialog.error(response.msg);
							}else if(response.state==1){
								$.ligerDialog.success(top.il8n.AJAX.done);
								basic_permission.grid.loadData();
							}
						},
						error : function(){
							manager.close();
							$.ligerDialog.error(top.il8n.AJAX.disConnect);
						}
					});
				}
			});
		}
		$.ligerui.get("m_name").setValue(selected.name);
		$.ligerui.get("m_rank").setValue(selected.rank);
		$.ligerui.get("m_icon").setValue(selected.icon);
		$.ligerui.get("m_remark").setValue(selected.remark);
		$.ligerui.get("m_path").setValue(selected.path);
	}
	
	,upload : function(){
		var dialog;
		if($.ligerui.get("basic_permission__grid_upload_d")){
			dialog = $.ligerui.get("basic_permission__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='basic_permission__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('basic_permission__grid_file'),
				action: '../php/myApp.php?class=basic_permission&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/basic_permission__add.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					basic_permission.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "basic_permission__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_permission__grid_file"),
				modal : true
			});
		}
	}
	
	,download: function(){
		var dialog;
		if($.ligerui.get("basic_permission__grid_download_d")){
			dialog = $.ligerui.get("basic_permission__grid_download_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='basic_permission__grid_download'></div>"));   
			$.ligerDialog.open({
				title: top.il8n.exportFile,
				id : "basic_permission__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#basic_permission__grid_download"),
				modal : true
			});
		}
		$.ajax({
			url : "../php/myApp.php?class=basic_permission&function=downloadAll",
			type : "POST",
			dataType: 'json',
			data: {username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
				 },
			success : function(response) {
				if(response.state==0){
					$.ligerDialog.error(response.msg);
				}else if(response.state==1){
					$("#basic_permission__grid_download").append("<a target='_blank' href='"+response.path+"'>"+response.file+"</a>");
				}
			},
			error : function(){
				manager.close();
				$.ligerDialog.error(top.il8n.AJAX.disConnect);
			}
		});		
	}
}