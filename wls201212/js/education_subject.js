var education_subject = {
	
	version: "2012X1"
		
	,id : 0 
	
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: myAppServer()+ "&class=education_subject&function=loadConfig",
			dataType: 'json',
			success : function(response) {
				education_subject.config = response;
				if ( typeof(afterAjax) == "string" ){
					eval(afterAjax);
				}
			},
			error : function(){				
				alert(top.il8n.disConnect);
			}
		});	
	}

	,grid: null
	,initGrid : function(){
		var config = {
			columns: [
				 { display: top.il8n.title, name: 'name', align: 'left', width: 140, minWidth: 60}
				,{ display: top.il8n.education_subject.code, name: 'code' ,align: 'left',width: 50 ,isSort : false}
				,{ display: top.il8n.type, name: 'type', isSort: false, render: function(a,b){
					for(var i=0; i<education_subject.config.type.length; i++){
						if(education_subject.config.type[i].code == a.type){
							return education_subject.config.type[i].value;
						}
					}
				 } }	
				,{ display: top.il8n.education_subject.count_papers, name: 'count_papers'}
				,{ display: top.il8n.education_subject.count_questions, name: 'count_questions'}
				,{ display: top.il8n.remark, name: 'remark', hide: true }
			], height:'100%',usePager:false,
			parms : {username : top.basic_user.username,
				session : MD5( top.basic_user.session +((new Date()).getHours()))},
			url : "../php/myApp.php?class=education_subject&function=getGrid",
			method  : "POST",
			id : "education_subject__grid",
			toolbar: { items: [] }
		};
		
		
		var permission = top.basic_user.permission;
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='13'){
				permission = permission[i].children;				
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1301'){
				permission = permission[i].children;				
			}
		}		
		
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='120201'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_subject.upload();
					}
				});
			}else if(permission[i].code=='120203'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_subject.download();
					}
				});
			}else if(permission[i].code=='130122'){
				//若可以执行删除操作,则必定是多选批量删除,则列表右侧应该提供多选勾选框
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_subject.delet();
					}
				});
			}else if(permission[i].code=='130121'){
				//若可以执行删除操作,则必定是多选批量删除,则列表右侧应该提供多选勾选框
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_subject.insert();
					}
				});
			}else if(permission[i].code=='130123'){
				//若可以执行删除操作,则必定是多选批量删除,则列表右侧应该提供多选勾选框
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_subject.update();
					}
				});
			}
		}
		
		
		this.grid = $(document.body).ligerGrid(config);
	}
	
};