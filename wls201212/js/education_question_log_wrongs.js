/**
 * 学生的错题本
 * 错题本功能,只有可能是 学生 这个用户组才能用
 * 访客不可用,教师也不能用,管理员也不可用
 * 
 * @version 201211
 * @author wei1224hf@gmail.com
 * */
var education_question_log_wrongs = {
	version: '201211'
		
	,gridPermissions: []
	,gridColmuns: [
	     {display: top.il8n.education_paper_log_wrongs.title, name: 'title', align: 'left'}
	    ,{display: top.il8n.education_paper_log_wrongs.paperTitle, name: 'paperTitle', align: 'left'}
	    ,{display: top.il8n.education_paper_log_wrongs.subject, name: 'subject', align: 'left'}
	    ,{display: top.il8n.education_paper_log_wrongs.rank, name: 'rank', align: 'left'}
	    ,{display: top.il8n.education_paper_log_wrongs.time_created, name: 'time_created', align: 'left'}
	]
	
	/**
	 * 在页面上初始化错题本这个列表
	 * */
	,grid: function(){
		var config = {
			height:'98%'
			,columns: education_question_log_wrongs.gridColmuns
			,pageSize:20 
			,rownumbers:true,
			url : myAppServer+"&class=education_question_log_wrongs&function=getGrid",
			method  : "POST",
			id : "education_question_log_wrongs",
			parms : education_question_log_wrongs.searchOptions,
			toolbar: { items: [] }
		};
		
		if( education_question_log_wrongs.gridPermissions.length==0 ){
			var permission = top.basic.user.permission;
			for(var i=0;i<permission.length;i++){
				if(permission[i].code=='14'){
					permission = permission[i].children;
				}
			}
			for(var i=0;i<permission.length;i++){
				if(permission[i].code=='1402'){
					permission = permission[i].children;
				}
			}
			education_question_log_wrongs.gridPermissions = permission;
		}
		
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='140201'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_paper.getSearchForm();
					}
				});
			}
		}

		education_question_log_wrongs.grid = $(document.body).ligerGrid(config);
	},
	
	searchOptions : {},
	getSearchForm : function(){
		var formD;
		if($.ligerui.get("formD")){
			formD = $.ligerui.get("formD");
			formD.show();
		}else{
			var form = $("<form id='form'></form>");
			$(form).ligerForm({
				inputWidth: 170, labelWidth: 90, space: 40,
				fields: [
					{ display: top.il8n.title, name: "title", newline: false, type: "text" },
					{ display: top.il8n.education.subject.subject, name: "subject", newline: true, type: "select", comboboxName: "combo_select", options: { url:'../php/myApp.php?class=education_subject&function=getSelectList', valueField : "code" , textField : "value",slide:false } },
					{ display: top.il8n.money, name: "money", newline: true, type: "number"  }
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
						$.ligerui.get("education_question_log_wrongs_grid").options.parms = {	};
						$.ligerui.get("education_question_log_wrongs_grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("money").setValue('');
						$.ligerui.get("combo_select").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
					$.ligerui.get("education_question_log_wrongs_grid").options.parms = {
						searchJson : $.ligerui.toJSON({
							name : $.ligerui.get("title").getValue(),
							subject : $.ligerui.get("combo_select").getValue(),
							money : $.ligerui.get("money").getValue(),
							foo : 'bar'
						})
					};
					$.ligerui.get("education_question_log_wrongs_grid").loadData();
				}}]
			});
		}
	}
};