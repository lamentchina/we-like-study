/**
 * 在线考试模块
 * 
 * 各个用户组看到的列,以及可以执行的操作都是不一样的
 * 
 * 教师可以看到的列有:
 *   试卷标题,开卷时间,闭卷时间,创建时间,科目,总分,状态,类型,模式
 * 教师只能看到他提交的,或者他下属单位提交的试卷,数据来源 education_exam
 * 教师可以执行的操作有:
 *   新建,删除,修改,导入,导出,批改,查询,查看
 *   
 * 学生可以看到的列有:
 *   试卷标题,开卷时间,闭卷时间,科目,我的班级排名,我的总排名,分数/总分,状态,类型,模式
 * 学生可以执行的操作有:
 *   查看整体排名单
 *   查看班级排名单
 *   参加考试
 *   查询历史考级记录
 *   查看
 * 
 * @author wei1224hf
 * @version 201212
 * */
var education_exam = {
		
	version: '201209'	
	
	/**
	 * 配置文件中,包含 下拉列表内容, 用户角色(教师,还是学生)
	 * 下拉列表内容(类别型数据),包含: 考试模式,考试类型,试卷状态(对学生),试卷状态(对教师)
	 * */
	,config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			 url: myAppServer()+ "&class=education_exam&function=loadConfig"
	        ,dataType: 'json'
	        ,type: "POST"
	        ,data: {
	             username: top.basic_user.username
	            ,userid: top.basic_user.loginData.id
	            ,usergroup: top.basic_user.loginData.id_group
	            ,usertype: top.basic_user.loginData.type
	        } 
			,success : function(response) {
				education_exam.config = response;
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

	/**
	 * 教师看到的列,跟学生看到的列是不一样的
	 * 先根据 config.role 来判断当前用户的角色
	 * 是学生,就启用 studentColumns
	 * 是教师,就启用 teacherColumns
	 * 
	 * 教师跟学生看到的不同列有:
	 *    教师可以执行的操作有: 修改试卷(试卷标题,参加班级,开始结束时间)
	 *    教师也有类型区别,领导可以对下属的数据执行 修改,删除 操作(这个不好操作)
	 *    教师关心的考试内容有: 试卷标题,开始时间,结束时间,班级总数,学生总数,科目,状态(编辑,发布,考试中,作废)
	 *        可以执行的操作有: 新增,上传导入,修改,删除,发布(发布后,就只能 '作废' ,作废不等于删除,会在试卷列表上保留记录 ),查看,查询
	 *    学生关心的列有      : 试卷标题,开始结束时间,状态(未考,已考,及格,不及格,旷考,作弊),班级排名,总排名(多用户组中),科目
	 *        可以执行的操作有: 做试卷,查看,查询
	 *    
	 * 还有查看功能
	 *    查看功能是最基本的,每个用户角色都能看到,不过,不同的角色看到的应该不一样
	 * */
	,grid : function(){		
		var gridColmuns = [
			[//管理员列
			 	{ display: top.il8n.education_exam.title, name: 'title', isSort: false }
			]
			,[//学生列
				 { display: top.il8n.education_exam.title, name: 'title', isSort: false }			
				,{ display: top.il8n.education_exam.title, name: 'subject', isSort: false }			
			  ]			
			,[//教师列
			     { display: top.il8n.education_exam.title, name: 'title', isSort: false }
				,{ display: top.il8n.education_exam.place, name: 'place', isSort: false, hide: true }				
				,{ display: top.il8n.education_exam.count_students, name: 'count_students'  ,isSort : false}
				,{ display: top.il8n.education_exam.count_passed, name: 'count_passed'  ,isSort : false}
				,{ display: top.il8n.education_exam.time_start, name: 'time_start'  ,isSort : false}
				,{ display: top.il8n.education_exam.time_end, name: 'time_end'  ,isSort : false}
				,{ display: top.il8n.education_exam.score, name: 'score'  ,isSort : false}
				,{ display: top.il8n.education_exam.mode, name: 'mode', render: function(a,b){
					for(var i=0; i<education_exam.config.type.length; i++){
						if(education_exam.config.mode[i].code == a.mode){
							return education_exam.config.mode[i].value;
						}
					}
				 }, hide: true }
				,{ display: top.il8n.education_exam.type, name: 'type', render: function(a,b){
					for(var i=0; i<education_exam.config.type.length; i++){
						if(education_exam.config.type[i].code == a.type){
							return education_exam.config.type[i].value;
						}
					}
				 } }	
				,{ display: top.il8n.education_exam.subject, name: 'subject', render: function(a,b){
					for(var i=0; i<education_exam.config.subject.length; i++){
						if(education_exam.config.subject[i].code == a.subject){
							return education_exam.config.subject[i].value;
						}
					}
				} }					
				,{ display: top.il8n.education_exam.teacher_name, name: 'teacher_name'  ,isSort : false}			  
			]			
		];

		var config = {
			height:'100%',
			pageSize:20 ,rownumbers:true,
			url : myAppServer() +"&class=education_exam&function=grid",
			method  : "POST",
			id : "education_exam_grid",
			parms : {
                 username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( education_exam.searchOptions )
                ,userid: top.basic_user.loginData.id
                ,usergroup: top.basic_user.loginData.id_group
                ,usertype: top.basic_user.loginData.type    
			},
			toolbar: { items: [] }
		};		

		config.columns = gridColmuns[(top.basic_user.loginData.type)*1-1];  		
		
		var permission = [];
		for(var i=0;i<top.basic_user.permission.length;i++){
			if(top.basic_user.permission[i].code=='16'){
				permission = top.basic_user.permission[i].children;
			}
		}
		
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1601'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_exam.search();
					}
				});
			}else if(permission[i].code=='1611'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_exam.upload();
					}
				});
			}else if(permission[i].code=='1603'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						var id = education_exam.grid.getSelected().id;

					}
				});
			}else if(permission[i].code=='1622'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_exam.delet();
					}
				});
			}else if(permission[i].code=='1605'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1690'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name, img:permission[i].icon , click : function(){
						var id = education_exam.grid.getSelected().id;
						if(top.$.ligerui.get("win_paper_"+id)){
							top.$.ligerui.get("win_paper_"+id).show();
							return;
						}
						top.$.ligerDialog.open({
							isHidden:false,
							id : "win_paper_"+id , height:  550, width: 600,
							url: "education_exam__do.html?id="+id,  
							showMax: true, showToggle: true, showMin: true, isResize: true,
							modal: false, title: education_exam.grid.getSelected().title, slide: false
	
						}).max();
						
						top.$.ligerui.get("win_paper_"+id).close = function(){
							var g = this, p = this.options;
							top.$.ligerui.win.removeTask(this);
							g.unmask();
							g._removeDialog();
							top.$.ligerui.remove(top.$.ligerui.get("win_paper_"+id));
							top.$('body').unbind('keydown.dialog');
						}
					}
				});
			}else if(permission[i].code=='1607'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
				
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1608'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						var arr = education_exam.grid.getCheckedRows();
						if(arr.length==0){
							$.ligerDialog.error(il8n.GRID.noSelect);
							return;
						}
						var url = 'education_exam__do.html?id='+arr[0].id;
						if(top===self){
							$.ligerDialog.open({
								id : "paper" , height:  550, url: url, width: 650, title: title, slide: false,
								showMax: true, showToggle: true, showMin: true, isResize: true, modal: false
							});
						}else{
							top.desktop.f_open(url,arr[0].name,"../file"+permission[i].icon,4);
						}
						win.max();
					}
				});
			}else if(permission[i].code=='1609'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1610'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}
		}
		
		$(document.body).ligerGrid(config);
	}

	/**
	 * 查询条件也要根据用户类型而定
	 * */
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
					{ display: top.il8n.title, name: "title", newline: false, type: "text" },
					{ display: top.il8n.education_subject.subject, name: "subject", newline: true, type: "select", comboboxName: "combo_select", options: { url:'../php/myApp.php?class=education_subject&function=getList', valueField : "code" , textField : "name",slide:false } },
					{ display: top.il8n.money, name: "money", newline: true, type: "text"  }
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
						$.ligerui.get("education_exam_grid").options.parms = {	};
						$.ligerui.get("education_exam_grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("money").setValue('');
						$.ligerui.get("combo_select").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
					$.ligerui.get("education_exam_grid").options.parms = {
						searchJson : $.ligerui.toJSON({
							name : $.ligerui.get("title").getValue(),
							subject : $.ligerui.get("combo_select").getValue(),
							money : $.ligerui.get("money").getValue(),
							foo : 'bar'
						})
					};
					$.ligerui.get("education_exam_grid").loadData();
				}}]
			});
		}
	}
	
	,upload : function(){
		var dialog;
		if($.ligerui.get("education_exam__grid_upload_d")){
			dialog = $.ligerui.get("education_exam__grid_upload_d");
			dialog.show();
		}else{
			$(document.body).append( $("<div id='education_exam__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('education_exam__grid_file')
				,action: myAppServer()+ "&class=education_exam&function=import"
				,allowedExtensions: ["xls"]
				,params: {
	                 username: top.basic_user.username
	                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
	                ,userid: top.basic_user.loginData.id
	                ,usergroup: top.basic_user.loginData.id_group
	                ,usertype: top.basic_user.loginData.type  
					,downloadExampleFile : "../file/download/education_exam.xls"
					,debug: true}
				,onComplete: function(id, fileName, responseJSON){
					education_exam.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "education_exam__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#education_exam__grid_file"),
				modal : true
			});
		}
	}	
	
	,delet: function(){
		//判断 ligerGrid 中,被勾选了的数据
		selected = education_exam.grid.getSelecteds();
		//如果一行都没有选中,就报错并退出函数
		if(selected.length==0){alert(il8n.noSelect);return;}
		//弹框让用户最后确认一下,是否真的需要删除.一旦删除,数据将不可恢复
		if(confirm(il8n.sureToDelete)){
			var ids = "";
			//遍历每一行元素,获得 id 
			for(var i=0; i<selected.length; i++){
				ids += selected[i].id+","
			}
			ids = ids.substring(0,ids.length-1);				
			
			$.ajax({
				url: myAppServer() + "&class=education_exam&function=delete",
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
						education_exam.grid.loadData();
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
	 * 查看一张试卷
	 *   如果是教师
	 * */
	,view: function(){}
};

var exam = paper;
exam.readExam = function(){
	var id = getParameter("id", window.location.toString() );
	
	$.ajax({
        url : myAppServer() + "&class=education_exam&function=getOne&id="+id
       ,type : "POST"
       ,data : {
            username: top.basic_user.username
           ,userid: top.basic_user.loginData.id
           ,usergroup: top.basic_user.loginData.id_group
           ,usertype: top.basic_user.loginData.type
       }      
       ,dataType: 'json'
       ,success : function(data) {                
           exam.cent = data.cent;
           exam.count.total = data.count_questions;
           exam.subjectCode = data.subjectCode;
           exam.subjectName = data.subject;
           exam.title = data.title;
           exam.cost = parseInt(data.cost);
           exam.setPaperBrief();
           
           if ( typeof(afterAjax) == "string" ){
               eval(afterAjax);
           }else if( typeof(afterAjax) == "function"){
               afterAjax();
           }				
       }
       ,error : function(){
           $.ligerDialog.error('网络通信失败');
       }
   });
}