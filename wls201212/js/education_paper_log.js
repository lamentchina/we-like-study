/**
 * 做题记录模块的前端JS功能
 * 
 * 学生的每一次做卷子,不论是普通的练习卷,多人在线考试试卷,统考试卷 等等,
 * 都会在 education_paper_log 表中记录,
 * 其中, 普通练习卷 不会在 education_question_log 中留下记录,
 * 普通练习卷模式下,学生提交了做题答案后,会先往 education_question_log 中插入记录,然后再删除
 * 
 * 教师可以制作试卷,但是无法提交答案或者做试卷,也就是说,
 * 教师不会在 education_paper_log education_question_log 中生成记录,
 * 那两张表只有 学生 这种用户角色才能对这两张表有 写入 的操作
 * 但这不表示教师没有 读 的权限.
 * 教师可以查看到他所管辖内所有学生的做题日志
 * 
 * 日志表,在前端对其的操作,只有 查询 查看详细 功能,
 * 前端无法对其做 删除 或 修改 功能
 * 
 * 前端列表中,教师可以看到的列有:
 *   试卷编号(默认隐藏),做题记录编号(默认隐藏),学生姓名,学生账号(默认隐藏),学生班级名称,类型
 *   试卷名称,科目,做题时间,状态[自动批改,待批改,已批改],类型[普通练习,多人在线考试,统考]
 * 教师可以执行的操作有:
 *   查询,批改,查看
 * 只有具有任课关系的教师才可以看到,没有任课关系的教师只能看到一张空表
 * 教师只能看到需要批改的试卷,全部自动批改的卷子看不到
 *   
 * 学生可以看到的列有:
 *   试卷名称,科目,做题时间,客观题成绩,主观题成绩,批卷时间,类型,状态
 * 可以执行的操作有:
 *   查询,查看做题记录
 *   
 * TODO
 * 管理员可以看到的列有:
 * 可以执行的操作有:
 *   查询,查看,批改
 *   
 * 此功能模块涉及的前端功能页面点有:
 *   教师中心.试卷批改
 *   学生中心.做题日志
 *   
 * @version 201212
 * @author wei1224hf@gmail.com
 * */
var education_paper_log = {
	version: '201211'    
	        
	,ajaxState: false        
	
	/**
	 * 配置文件中,包含 下拉列表内容, 用户角色(教师,还是学生)
	 * 下拉列表内容(类别型数据),包含: 试卷类型,试卷状态(对学生),试卷状态(对教师)
	 * */    
	,config: null
	,loadConfig: function(afterAjax){
	    $.ajax({
	        url: myAppServer()+ "&class=education_paper_log&function=loadConfig"
	        ,dataType: 'json'
	        ,type: "POST"
	        ,data: {
	             username: top.basic_user.username
	            ,userid: top.basic_user.loginData.id
	            ,usergroup: top.basic_user.loginData.id_group
	            ,usertype: top.basic_user.loginData.type
	        }        
	        ,success : function(response) {
	            education_paper_log.config = response;
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
	
	,grid : function(){
    	var gridColmuns = [
    	   [//管理员列
    	   
    	   ]
           ,[//教师列
   	         { display: top.il8n.education_paper_log.paperid, name: 'paperid',hide:true }
   	        ,{ display: top.il8n.education_paper_log.id, name: 'id',hide:true }
   	        ,{ display: top.il8n.education_paper_log.studentname, name: 'studentname' }
   	        ,{ display: top.il8n.education_paper_log.studentcode, name: 'studentcode',hide:true }
   	        ,{ display: top.il8n.education_paper_log.studentclass, name: 'studentclass' }
   	        ,{ display: top.il8n.education_paper_log.papertitle, name: 'papertitle' }
   	        ,{ display: top.il8n.education_paper_log.time_created, name: 'time_created',hide:true }   	     
	        ,{ display: top.il8n.education_paper_log.type, name: 'type', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.type.length; i++){
					if(education_paper_log.config.type[i].code == a.type){
						return education_paper_log.config.type[i].value;
					}
				}
			} }
	        ,{ display: top.il8n.education_paper_log.subject, name: 'subject', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.subject.length; i++){
					if(education_paper_log.config.subject[i].code == a.subjectcode){
						return education_paper_log.config.subject[i].value;
					}
				}
			} } 	
	        ,{ display: top.il8n.education_paper_log.status, name: 'status', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.status.length; i++){
					if(education_paper_log.config.status[i].code == a.status){
						return education_paper_log.config.status[i].value;
					}
				}
			} }    	        
           ]
           ,[//学生列
   	         { display: top.il8n.education_paper_log.paperid, name: 'paperid',hide:true }
	        ,{ display: top.il8n.education_paper_log.id, name: 'id',hide:true }
	        ,{ display: top.il8n.education_paper_log.score_subjective, name: 'score_subjective' }
	        ,{ display: top.il8n.education_paper_log.score_objective, name: 'score_objective',hide:true }
	        ,{ display: top.il8n.education_paper_log.time_created, name: 'time_created' }  
	        ,{ display: top.il8n.education_paper_log.papertitle, name: 'papertitle' }	        
	        ,{ display: top.il8n.education_paper_log.type, name: 'type', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.type.length; i++){
					if(education_paper_log.config.type[i].code == a.type){
						return education_paper_log.config.type[i].value;
					}
				}
			} }
	        ,{ display: top.il8n.education_paper_log.subject, name: 'subject', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.subject.length; i++){
					if(education_paper_log.config.subject[i].code == a.subjectcode){
						return education_paper_log.config.subject[i].value;
					}
				}
			} } 	
	        ,{ display: top.il8n.education_paper_log.status, name: 'status', render: function(a,b){
	        	for(var i=0; i<education_paper_log.config.status.length; i++){
					if(education_paper_log.config.status[i].code == a.status){
						return education_paper_log.config.status[i].value;
					}
				}
			} } 
           ]
        ];
		
        var config = {
            height:'100%',
            columns: [],  pageSize:20 ,rownumbers:true,
            url : myAppServer()+ "&class=education_paper_log&function=grid",
            method  : "POST",
            id : "education_paper_log_grid",
            parms : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( education_paper_log.searchOptions )
                ,userid: top.basic_user.loginData.id
                ,usergroup: top.basic_user.loginData.id_group
                ,usertype: top.basic_user.loginData.type                
            },
            toolbar: { items: [] }
        };
		
        if(top.basic_user.loginData.type==='1'){
        	config.columns = gridColmuns[0];
        }
        if(top.basic_user.loginData.type==='2'){
        	config.columns = gridColmuns[2];
        }
        if(top.basic_user.loginData.type==='3'){
        	config.columns = gridColmuns[1];
        }        
        
		var permission = [];
		for(var i=0;i<top.basic_user.permission.length;i++){
			if(top.basic_user.permission[i].code=='19'){
				permission = top.basic_user.permission[i].children;
			}
		}
		
        for(var i=0;i<permission.length;i++){
            if(permission[i].code=='1901'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
                        education_paper_log.search();
                    },disable:true
                });
            }else if(permission[i].code=='1902'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){

                        var id = $.ligerui.get('education_paper_log_grid').getSelected().id;
                        if(top.$.ligerui.get("win_paper_log_"+id)){
                            top.$.ligerui.get("win_paper_log_"+id).show();
                            return;
                        }
                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_paper_log_"+id , height:  550, width: 600,
                            title: "做题日志"+id,
                            url: "education_paper_log__view.html?id="+id,  
                            showMax: true, showToggle: true, showMin: true, isResize: true,
                            modal: false, 
                            slide: false
    
                        }).max();
                        
                        top.$.ligerui.get("win_paper_log_"+id).close = function(){
                            var g = this, p = this.options;
                            top.$.ligerui.win.removeTask(this);
                            g.unmask();
                            g._removeDialog();
                            top.$.ligerui.remove(top.$.ligerui.get("win_paper_log_"+id));
                            top.$('body').unbind('keydown.dialog');
                        }
                    }
                });
            }
        }
		
		$(document.body).ligerGrid(config);
	}

	
	,searchOptions : {}
	
	,search : function(){
		var form_epl_search;
		if($.ligerui.get("form_epl_search")){
			form_epl_search = $.ligerui.get("form_epl_search");
			form_epl_search.show();
		}else{
			var form = $("<form></form>");
			$(form).ligerForm({
				inputWidth: 170, labelWidth: 90, space: 40,
				fields: [	
	                 { display: top.il8n.title, name: "title", newline: false, type: "text" }
	                ,{ display: top.il8n.education_paper_log.subject, name: "subject"
	                	, newline: true, type: "select", comboboxName: "combo_select"
	                	, options: { 
	                		data:education_paper_log.config.subject
	                		,valueField : "code" , textField : "value",slide:false }	                
	                }
				]
			}); 
			$.ligerDialog.open({
				id : "form_epl_search",
				width : 350,
				height : 150,
				content : form,
				title : top.il8n.search,
				buttons : [
				    //清空查询条件
					{text:top.il8n.clear,onclick:function(){
                        $.ligerui.get("education_paper_log_grid").options.parms.search =  $.ligerui.toJSON({foo:1});
                        $.ligerui.get("education_paper_log_grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("combo_select").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
				    	$.ligerui.get("education_paper_log_grid").options.parms.search =  $.ligerui.toJSON({					
							title : $.ligerui.get("title").getValue(),
							subject : $.ligerui.get("combo_select").getValue(),
							foo : 'bar'
						});					
				    	$.ligerui.get("education_paper_log_grid").loadData();
				    }}
				]
			});
		}
	}
	
	,upload : function(){
		var dialog;
		if($.ligerui.get("education_paper_log__grid_upload_d")){
			dialog = $.ligerui.get("education_paper_log__grid_upload_d");
			dialog.show();
		}else{

			$(document.body).append( $("<div id='education_paper_log__grid_file'></div>"));
			var uploader = new qq.FileUploader({
				element: document.getElementById('education_paper_log__grid_file'),
				action: '../php/myApp.php?class=education_paper_log&function=import',
				allowedExtensions: ["xls"],
				params: {username: top.basic_user.username,
					session: MD5( top.basic_user.session +((new Date()).getHours()))},
				downloadExampleFile : "../file/download/education_paper_log.xls",
				debug: true,
				onComplete: function(id, fileName, responseJSON){
					education_paper_log.grid.loadData();
				}
	        });    
			
			$.ligerDialog.open({
				title: top.il8n.importFile,
				
				id : "education_paper_log__grid_upload_d",
				width : 350,
				height : 200,
				target : $("#education_paper_log__grid_file"),
				modal : true
			});
		}
	}	
	
	,view: function(){
		var logid = getParameter("id", window.location.toString() );
		paperlog.readlog(logid);
	}
};

var paperlog = paper;
paperlog.readlog = function(logid){
	 $.ajax({
         url :  myAppServer()+ "&class=education_paper_log&function=view&id="+logid
        ,type : "POST"
        ,data : {
             username: top.basic_user.username
            ,session: MD5( top.basic_user.session +((new Date()).getHours()))
            ,userid: top.basic_user.loginData.id
            ,usergroup: top.basic_user.loginData.id_group
            ,usertype: top.basic_user.loginData.type   
        }      
        ,dataType: 'json'
        ,success : function(response) {
            var data = response.paper;                
            paperlog.initLayout();

            paperlog.cent = data.cent;
            paperlog.count.total = data.count_questions;
            paperlog.subjectCode = data.subject;
            paperlog.subjectName = data.subject;
            paperlog.title = data.title;
            paperlog.cost = parseInt(data.cost);
            paperlog.setPaperBrief();
            
            var quesData = response.question;
            var index = 1;
            for(var i=0;i<quesData.length;i++){
                var question = null;
                if(quesData[i].type==1){//单项选择题
                    question = new question_choice();
                    question.optionLength = quesData[i].optionlength;
                    question.options = [];
                    for(var ii=1;ii<=parseInt(quesData[i].optionlength);ii++){
                        eval("question.options.push(quesData[i].option"+ii+")");
                    }
                    question.index = index;index++;
                    question.layout = quesData[i].layout;
                    question.title = quesData[i].title; 
                    
                }                    
                else if(quesData[i].type==2){//多项选择题
                    question = new question_multichoice();
                    question.optionLength = quesData[i].optionlength;
                    question.index = index;index++;
                    question.layout = quesData[i].layout;
                    question.title = quesData[i].title;
                    question.options = [];
                    for(var ii=1;ii<=parseInt(quesData[i].optionlength);ii++){
                        eval("question.options.push(quesData[i].option"+ii+")");
                    }
                }
                else if(quesData[i].type==3){//判断题
                    question = new question_check();
                    question.index = index;index++;
                    question.layout = quesData[i].layout;
                    question.title = quesData[i].title;
                    question.options = [quesData[i].option1,quesData[i].option2];
                }else if(quesData[i].type==7){//大题, 不需要题编号
                    question = new question_big();
                    question.title = quesData[i].title;
                }else if(quesData[i].type==4){//填空题
                    question = new question_blank();
                    if(quesData[i].cent!=0){//填空题题干不需要题编号
                        question.index = index;
                        index++;
                    }
                    question.title = quesData[i].title;
                }else if(quesData[i].type==5){//组合题, 不需要题编号
                    question = new question_mixed();
                    question.title = quesData[i].title;
                }else{
                    continue;
                }
                //console.debug(index+"     "+quesData[i].type);
                question.type = quesData[i].type;
                question.answer = quesData[i].answer;
                question.myAnswer = quesData[i].myanswer;
                question.description = quesData[i].description;
                question.path_listen = quesData[i].path_listen;
                question.cent = quesData[i].cent;
                question.id = quesData[i].id;
                question.id_parent = quesData[i].id_parent;
                question.paper = paper;
                paperlog.questions.push(question);
            }
            paperlog.initQuestions();
            
            for(var i=0;i < paperlog.questions.length;i++){
            	paperlog.questions[i].setMyAnswer();
                paperlog.questions[i].showDescription();
            }

        }
        ,error : function(){
            $.ligerDialog.error('网络通信失败');
        }
    });
}
