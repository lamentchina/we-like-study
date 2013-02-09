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
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
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
			   { display: top.il8n.id, name: 'id', isSort: false, hide:true }
			  ,{ display: top.il8n.title, name: 'title', isSort: false, width: 140 }
			  ,{ display: top.il8n.education_exam.count_students, name: 'count_students', isSort: false, width: 100 }
			  ,{ display: top.il8n.education_exam.count_submit, name: 'count_submit', isSort: false }
			  ,{ display: top.il8n.education_exam.teacher_name, name: 'teacher_name', isSort: false }
			  ,{ display: top.il8n.education_exam.subject_code, name: 'subject_code', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.subject_name, name: 'subject_name', isSort: false }
			  ,{ display: top.il8n.education_exam.passline, name: 'passline', isSort: false, width:60 }
			  ,{ display: top.il8n.education_exam.teacher_id, name: 'teacher_id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.time_start, name: 'time_start', isSort: false, width: 80 }
			  ,{ display: top.il8n.education_exam.time_end, name: 'time_end', isSort: false, width: 80 }			  
			]
			,[//学生列
			   { display: top.il8n.education_exam.exam_id, name: 'exam_id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.exam_title, name: 'exam_title', isSort: false, width: 140 }
			  ,{ display: top.il8n.education_exam.teacher_name, name: 'teacher_name', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.subject_code, name: 'subject_code', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.subject_name, name: 'subject_name', isSort: false, width:60 }
			  ,{ display: top.il8n.education_exam.teacher_id, name: 'teacher_id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.rank, name: 'rank', isSort: false }
			  ,{ display: top.il8n.education_exam.rank_class, name: 'rank_class', isSort: false, width:60 }
			  ,{ display: top.il8n.education_exam.score, name: 'score', isSort: false }
			  ,{ display: top.il8n.education_exam.passline, name: 'passline', isSort: false, width:60 }
			  ,{ display: top.il8n.education_exam.totalcent, name: 'totalcent', isSort: false }
			  ,{ display: top.il8n.education_exam.id_paper, name: 'id_paper', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.id_paper_log, name: 'id_paper_log', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.time_start, name: 'time_start', isSort: false, width: 80 }
			  ,{ display: top.il8n.education_exam.time_end, name: 'time_end', isSort: false, width: 80 }
			  ,{ display: top.il8n.education_exam.time_submit, name: 'time_submit', isSort: false, hide:true  }
			  ,{ display: top.il8n.education_exam.time_mark, name: 'time_mark', isSort: false, hide:true  }
			  ,{ display: top.il8n.education_exam.type, name: 'name_type', isSort: false }			  
			  ,{ display: top.il8n.education_exam.status, name: 'name_status', isSort: false }
			  ,{ display: top.il8n.education_exam.type, name: 'type', isSort: false, hide:true }			  
			  ,{ display: top.il8n.education_exam.status, name: 'status', isSort: false, hide:true }				  
			  ,{ display: top.il8n.id, name: 'id', isSort: false, hide:true }
			  ]			
			,[//教师列
			   { display: top.il8n.id, name: 'id', isSort: false, hide:true }
			  ,{ display: top.il8n.title, name: 'title', isSort: false, width: 140 }
			  ,{ display: top.il8n.education_exam.teacher_name, name: 'teacher_name', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.subject_code, name: 'subject_code', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.subject_name, name: 'subject_name', isSort: false, width: 100 }
			  ,{ display: top.il8n.education_exam.teacher_id, name: 'teacher_id', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.count_submit, name: 'count_submit', isSort: false }
			  ,{ display: top.il8n.education_exam.totalcent, name: 'score', isSort: false }
			  ,{ display: top.il8n.education_exam.passline, name: 'passline', isSort: false }
			  ,{ display: top.il8n.education_exam.id_paper, name: 'id_paper', isSort: false, hide:true }
			  ,{ display: top.il8n.education_exam.time_start, name: 'time_start', isSort: false, width: 80 }
			  ,{ display: top.il8n.education_exam.time_end, name: 'time_end', isSort: false, width: 80 }
			  ,{ display: top.il8n.education_exam.type, name: 'name_type', isSort: false }			  
			  ,{ display: top.il8n.education_exam.status, name: 'name_status', isSort: false }	
			  ,{ display: top.il8n.education_exam.type, name: 'type', isSort: false, hide:true }			  
			  ,{ display: top.il8n.education_exam.status, name: 'status', isSort: false, hide:true }	
			  ,{ display: top.il8n.education_exam.mode, name: 'name_mode', isSort: false }	
			  ,{ display: top.il8n.education_exam.mode, name: 'mode', isSort: false, hide:true }			  
			]			
		];

		var config = {
			height:'100%',
			pageSize:20 ,rownumbers:true,
			url : myAppServer() +"&class=education_exam&function=grid",
			method  : "POST",
			id : "education_exam__grid",
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
			}else if(permission[i].code=='1602'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						var selected = null;
	                	if($.ligerui.get('education_exam__grid').options.checkbox){
	                		//启用了多行勾选
							selected = $.ligerui.get('education_exam__grid').getSelecteds();
							if(selected.length!=1){
								alert(top.il8n.selectOne);return;
							}
							selected = selected[0];
	                	}else{
	                		selected = $.ligerui.get('education_exam__grid').getSelected();
							if(selected==null){
								alert(top.il8n.noSelect);return;
							}
	                	}
	                	
	                	var id = selected.id;
	                	var title = selected.name;
	                    if(top.$.ligerui.get("education_exam__view_win_"+id)){
	                        top.$.ligerui.get("education_exam__view_win_"+id).show();
	                        return;
	                    }
	                    top.$.ligerDialog.open({
	                        isHidden:false
	                        ,id: "education_exam__view_win_"+id 
	                        ,height: 550
	                        ,width: 600
	                        ,url: "education_exam__view.html?id="+id
	                        ,showMax: true
	                        ,showToggle: true
	                        ,showMin: true
	                        ,isResize: true
	                        ,modal: false
	                        ,title: title
	                        ,slide: false    	
	                    }).max();
	                    
	                    top.$.ligerui.get("education_exam__view_win_"+id).close = function(){
	                        var g = this, p = this.options;
	                        top.$.ligerui.win.removeTask(this);
	                        g.unmask();
	                        g._removeDialog();
	                        top.$.ligerui.remove(top.$.ligerui.get("education_exam__view_win_"+id));
	                        top.$('body').unbind('keydown.dialog');
	                    }
					}
					
				});
			}else if(permission[i].code=='1611'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon , click : function(){
						education_exam.upload();
					}
				});
			}else if(permission[i].code=='1612'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						//勾选一张试卷,导出数据,EXCEL下载
                    	var selected = null;
                    	if($.ligerui.get('education_exam__grid').options.checkbox){
                    		//启用了多行勾选
							selected = $.ligerui.get('education_exam__grid').getSelecteds();
							if(selected.length!=1){
								alert(top.il8n.selectOne);return;
							}
							selected = selected[0];
                    	}else{
                    		selected = $.ligerui.get('education_exam__grid').getSelected();
							if(selected==null){
								alert(top.il8n.noSelect);return;
							}
                    	}
                    	
                    	var id = selected.id;
                    	var title = selected.title;
                    	education_exam.export_(id,title);
					}
				});
			}else if(permission[i].code=='1621'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1622'){
				config.checkbox = true;
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_exam.delete_();
					}
				});
			}else if(permission[i].code=='1623'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1690'){
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name, img:permission[i].icon , click : function(){
						var status_ = $.ligerui.get('education_exam__grid').getSelected().status;
						if(status_!=22){
							alert('wrong status');return;
						}
						var id = $.ligerui.get('education_exam__grid').getSelected().exam_id;
						var id_paper = $.ligerui.get('education_exam__grid').getSelected().id_paper;
						var id_e2s = $.ligerui.get('education_exam__grid').getSelected().id;
						if(top.$.ligerui.get("win_exam_"+id)){
						    top.$.ligerui.get("win_exam_"+id).show();
						    return;
						}
						top.$.ligerDialog.open({
						    isHidden:false,
						    id : "win_exam_"+id , height:  550, width: 600,
						    url: "education_exam__do.html?id="+id+"&id_paper="+id_paper+"&id_e2s="+id_e2s,  
						    showMax: true, showToggle: true, showMin: true, isResize: true,
						    modal: false, title: $.ligerui.get('education_exam__grid').getSelected().exam_title, slide: false
						
						}).max();
						
						top.$.ligerui.get("win_exam_"+id).close = function(){
						    var g = this, p = this.options;
						    top.$.ligerui.win.removeTask(this);
						    g.unmask();
						    g._removeDialog();
						    top.$.ligerui.remove(top.$.ligerui.get("win_exam_"+id));
						    top.$('body').unbind('keydown.dialog');
						}
					}
				});
			}else if(permission[i].code=='1691'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1692'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1693'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						
					}
				});
			}else if(permission[i].code=='1694'){
				
				config.toolbar.items.push({line: true });
				config.toolbar.items.push({
					text: permission[i].name , img:permission[i].icon, click : function(){
						education_exam.mark();
					}
				});
			}else if(permission[i].code=='1695'){
				
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
					{ display: top.il8n.title, name: "title", newline: false, type: "text" }
					,{ display: top.il8n.education_subject.subject, name: "education_exam__search_subject", newline: true, type: "select", options :{data : education_exam.config.subject, valueField : "code" , textField: "value" } }
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
						$.ligerui.get("education_exam__grid").options.parms.search = {	};
						$.ligerui.get("education_exam__grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("education_exam__search_subject").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
					$.ligerui.get("education_exam__grid").options.parms.search = liger.toJSON({
						title : $.ligerui.get("title").getValue(),
						subject : $.ligerui.get("education_exam__search_subject").getValue()
					});
					$.ligerui.get("education_exam__grid").loadData();
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
					$.ligerui.get("education_exam__grid").loadData();
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
	
	/**
	 * 实现EXCEL导出
     * 先从业务表到 basic_excel 
     * 再从 basic_excel 到 .xls
     * 然后前端再下载
     * */
    ,export_: function(id,title){
    	var download_dom;
        if($.ligerui.get("download_dom")){
        	download_dom = $.ligerui.get("download_dom");        	
        	download_dom.show();
        }else{                    
            $.ligerDialog.open({
                id : "download_dom",
                width : 350,
                height : 150,
                content : "<div id='download_dom_'></div>",
                title : top.il8n.download
            });
        }    	
    	$.ajax({
            url: myAppServer() + "&class=education_exam&function=export",
            data: {
                id:id 
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code  
            },
            type: "POST",
            dataType: 'json',
            success: function(response) {
            	$('#download_dom_').append("<a href='"+response.file+"' target='_blank' >&nbsp;"+top.il8n.download+"&nbsp;"+title+"<br/></a>");
            },
            error : function(){               
                alert(top.il8n.disConnect);
            }
        });     
    }
	
	,delete_: function(){
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
	 * 教师批改试卷
	 * 一次只能批改一套试卷,无法批量处理
	 * */
	,mark: function(){
		var selected = null;
    	if($.ligerui.get('education_exam__grid').options.checkbox){
    		//启用了多行勾选
			selected = $.ligerui.get('education_exam__grid').getSelecteds();
			if(selected.length!=1){
				alert(top.il8n.selectOne);return;
			}
			selected = selected[0];
    	}else{
    		selected = $.ligerui.get('education_exam__grid').getSelected();
			if(selected==null){
				alert(top.il8n.noSelect);return;
			}
    	}
    	var id = selected.id;
    	
		if(confirm(il8n.areYouSure)){		
			$.ajax({
				url: myAppServer() + "&class=education_exam&function=mark",
				data: {
					 id: id 
					//服务端权限验证所需
	                ,username: top.basic_user.username
	                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
	                ,search: $.ligerui.toJSON( basic_user.searchOptions )
	                ,user_id: top.basic_user.loginData.id
	                ,user_type: top.basic_user.loginData.type    
	                ,group_id: top.basic_user.loginData.group_id
	                ,group_code: top.basic_user.loginData.group_code
				},
				type: "POST",
				dataType: 'json',
				success: function(response) {
					if(response.state==1){
						$.ligerui.get('education_exam__grid').loadData();
					}else{
						alert(response.msg);
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
	,view: function(){
		var id = getParameter("id", window.location.toString() );
    	$(document.body).html("<div id='buttons' style='width:95%' ></div><div id='content' style='width:98%;margin-top:5px;'></div>");
    	var htmls = "";
    	$.ajax({
            url: myAppServer() + "&class=education_exam&function=view",
            data: {
                id:id 
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code  
            },
            type: "POST",
            dataType: 'json',
            success: function(response) {
            	for(var j in response){   
            		if(j=='sql')continue;
            		if(j=='id'||j=='remark')htmls+="<div style='width:100%;float:left;display:block;margin-top:5px;'/>";
            		eval("var key = getIl8n('education_exam','"+j+"');");
            		htmls += "<span class='view_lable'>"+key+"</span><span class='view_data'>"+response[j]+"</span>";
            	}; 
            	$("#content").html(htmls);
            	
            	//查看详细,页面上也有按钮的
            	var items = [];            	
                var permission = top.basic_user.permission;
                for(var i=0;i<permission.length;i++){
                    if(permission[i].code=='15'){
                        permission = permission[i].children;
                        break;
                    }
                }      
                for(var i=0;i<permission.length;i++){
                    if(permission[i].code=='1502'){
                    	if(typeof(permission[i].children)=='undefined')return;
                        permission = permission[i].children;
                        break;
                    }
                }    
                
                for(var i=0;i<permission.length;i++){        	
                    if(permission[i].code=='150201'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon , click : function(){
                                
                            }
                        });
                    }else if(permission[i].code=='150202'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){                            	
                                
                            }
                        });
                    }else if(permission[i].code=='150222'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){
                                
                            }
                        });
                    }else if(permission[i].code=='150223'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){
                                
                            }
                        });
                    }
                }
                if(items.length>0){
	            	$("#buttons").ligerToolBar({
	            		items:items
	            	});
            	}
            },
            error : function(){               
                alert(top.il8n.disConnect);
            }
        });		
		
	}
};

var exam = paper;
exam.objName='exam';
exam.readExam = function(afterAjax){
	var id = getParameter("id", window.location.toString() );
	
	$.ajax({
        url : myAppServer() + "&class=education_exam&function=view&id="+id
       ,type : "POST"
       ,data : {
            username: top.basic_user.username
           ,session: MD5( top.basic_user.session +((new Date()).getHours()))
           ,search: $.ligerui.toJSON( basic_user.searchOptions )
           ,user_id: top.basic_user.loginData.id
           ,user_type: top.basic_user.loginData.type    
           ,group_id: top.basic_user.loginData.group_id
       }      
       ,dataType: 'json'
       ,success : function(data) {                
		   exam.cent = data.cent;
		   exam.count.total = data.count_questions;
		   exam.title = data.title;
		   exam.id_paper = data.id_paper;
		   
		   exam.brief = {
		       subject_name: data.subject_name
		       ,time_start: data.time_start
		       ,time_end: data.time_end
		       ,count_questions: data.count_questions
		       ,teacher_name: data.teacher_name
		       ,cent: data.cent
		   };
		   
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
exam.submit = function(){
	if(top.basic_user.loginData.type!='2'){
		alert("only student can submit");
		return;
	}
    if(this.state=='submitted'){
        alert("paper has submitted arleady");
        return;
    }
    this.state = 'submitted';
    $('#submit').val(top.il8n.waitting);

    var toSend = [];
    var ids = "";
    for(var i=0;i<this.questions.length;i++){
        toSend.push({
            id:this.questions[i].id,
            myanswer:this.questions[i].getMyAnswer()
        });
        ids += this.questions[i].id+",";//搜集所有题目的编号
    }        
    ids = ids.substring(0,ids.length-1);//去掉最后一个 ,  TODO 
    var thisObj = this;
    
    $.ajax({
        url : "../php/myApp.php?class=education_exam&function=submit",
        type : 'POST',
        data : {
             json: $.ligerui.toJSON(toSend)
            ,id: getParameter("id_e2s", window.location.toString() )
            ,id_paper: thisObj.id_paper
            ,username: top.basic_user.username
            ,session: MD5( top.basic_user.session +((new Date()).getHours()))
            ,search: $.ligerui.toJSON( basic_user.searchOptions )
            ,user_id: top.basic_user.loginData.id
            ,user_type: top.basic_user.loginData.type    
            ,group_id: top.basic_user.loginData.group_id
            ,group_code: top.basic_user.loginData.group_code
        }, 
        dataType: 'json',
        success : function(data) {
        	$('#submit').remove();
        	if(data.status!='1'){
        		alert(data.msg);
        	}else{
        		alert('submitted now , waitting for teacher\'s markking');
        	}
        }
    });
}