/**
 * 错题本的前端JS模块
 * 学生在 试卷模式 或 考试模式 下做题的时候,如果题目做错了,做错的题目会被记录下来
 * 学生可以打开自己的错题本,重新练习里面的错题
 * 在练习错题的时候,如果错题被作对两次,错题就会被删除
 * 错题数据可以被导出为 word 格式,
 * 便于学生线下复习
 * 
 * 错题本容量有限制,
 * 在盈利性系统的模式下,每一个学生的错题本容量都是不一样的:
 *   0 无限制的错题本容量
 *   100 错题本容量只有100题,如果已经达到100题,则不会再记录错题
 *   500 错题本容量500题
 * 在学生表 education_student 中, wrongsbook 这个int字段将作为错题本容量标识
 * 
 * 各种类型的用户,可以看到的表格列以及可以执行的操作有:
 * 管理员:
 *   看到系统所有用户的所有错题记录
 *   可以看到 题目标题,科目,教师,学生,创建时间,试卷标题
 *   执行的操作有 查询,删除,导出EXCEL,导出word,错题练习
 * 教师:
 *   可以看到作者是本人的题目的错题记录
 *   可以看到 题目标题,科目,教师,创建时间,试卷标题,做错总数,作对总数,难度评价,创建时间,学生,班级,
 * 
 *   
 * @version 201212
 * @author wei1224hf@gmail.com
 * */
var education_question_log_wrongs = {
	        
	ajaxState: false        
	
	/**
	 * 配置文件中,包含 下拉列表内容, 用户角色(教师,还是学生)
	 * 下拉列表内容(类别型数据),包含: 试卷类型,试卷状态(对学生),试卷状态(对教师)
	 * */    
	,config: null
	,loadConfig: function(afterAjax){
	    $.ajax({
	        url: myAppServer()+ "&class=education_question_log_wrongs&function=loadConfig"
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
	            education_question_log_wrongs.config = response;
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
  	         { display: top.il8n.title, name: 'paper_title', align: 'left', width: 140, minWidth: 60 }
   	        ,{ display: top.il8n.education_question_log_wrongs.subject, name: 'subject_name',isSort : false }
 	        ,{ display: top.il8n.education_question_log_wrongs.subject, name: 'subject_code',isSort : false, hide: true }
 	        ,{ display: top.il8n.education_question_log_wrongs.cent, name: 'cent' ,width: 50 ,isSort : false}
 	        ,{ display: top.il8n.education_question_log_wrongs.cost, name: 'cost' ,width: 50}
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_name' }
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_code',isSort : false, hide: true }
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_id',isSort : false, hide: true }
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'student_name' }
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'student_code',isSort : false, hide: true }
  		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'student_id',isSort : false, hide: true }
 	        ,{ display: top.il8n.time_created, name: 'time_created', width: 80 }  
           ]
           ,[//学生列
  	         { display: top.il8n.title, name: 'paper_title', align: 'left', width: 140, minWidth: 60 }
    	    ,{ display: top.il8n.education_question_log_wrongs.subject, name: 'subject_name',isSort : false }
  	        ,{ display: top.il8n.education_question_log_wrongs.subject, name: 'subject_code',isSort : false, hide: true }
  	        ,{ display: top.il8n.education_question_log_wrongs.cent, name: 'cent' ,width: 50 ,isSort : false}
  	        ,{ display: top.il8n.education_question_log_wrongs.mycent, name: 'mycent' ,width: 50}
   		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_name' }
   		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_code',isSort : false, hide: true }
   		   	,{ display: top.il8n.education_question_log_wrongs.teacher_name, name: 'teacher_id',isSort : false, hide: true }
   		   	,{ display: top.il8n.education_question_log_wrongs.student_name, name: 'student_name' }
   		   	,{ display: top.il8n.education_question_log_wrongs.student_name, name: 'student_code',isSort : false, hide: true }
   		   	,{ display: top.il8n.education_question_log_wrongs.student_name, name: 'student_id',isSort : false, hide: true }
  	        ,{ display: top.il8n.time_created, name: 'time_created', width: 80 } 
           ]
        ];
		
        var config = {
            height:'100%',
            columns: [],  pageSize:20 ,rownumbers:true,
            url : myAppServer()+ "&class=education_question_log_wrongs&function=grid",
            method  : "POST",
            id : "education_question_log_wrongs_grid",
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
		
        if(top.basic_user.loginData.type==='1'){
        	config.columns = gridColmuns[0];
        }
        if(top.basic_user.loginData.type==='2'){
        	config.columns = gridColmuns[1];
        }
        if(top.basic_user.loginData.type==='3'){
        	config.columns = gridColmuns[2];
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
                        education_question_log_wrongs.search();
                    },disable:true
                });
            }else if(permission[i].code=='1902'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){

                        var id = $.ligerui.get('education_question_log_wrongs_grid').getSelected().id;
                        var id_paper = $.ligerui.get('education_question_log_wrongs_grid').getSelected().paper_id;
                        if(top.$.ligerui.get("win_paper_log_"+id)){
                            top.$.ligerui.get("win_paper_log_"+id).show();
                            return;
                        }
                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_paper_log_"+id , height:  550, width: 600,
                            title: "做题日志"+id,
                            url: "education_question_log_wrongs__view.html?id="+id+"&id_paper="+id_paper,
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
	                ,{ display: top.il8n.education_question_log_wrongs.subject, name: "subject"
	                	, newline: true, type: "select", comboboxName: "combo_select"
	                	, options: { 
	                		data:education_question_log_wrongs.config.subject
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
                        $.ligerui.get("education_question_log_wrongs_grid").options.parms.search =  $.ligerui.toJSON({foo:1});
                        $.ligerui.get("education_question_log_wrongs_grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("combo_select").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
				    	$.ligerui.get("education_question_log_wrongs_grid").options.parms.search =  $.ligerui.toJSON({					
							title : $.ligerui.get("title").getValue(),
							subject : $.ligerui.get("combo_select").getValue(),
							foo : 'bar'
						});					
				    	$.ligerui.get("education_question_log_wrongs_grid").loadData();
				    }}
				]
			});
		}
	}	
	
	,view: function(){
		var logid = getParameter("id", window.location.toString() );
		paperlog.readlog(logid);
	}
};

var paperlog = paper;
paperlog.initLayout=function() {
    $(document.body).append(''+
    '<div id="layout1">         '+   
    '    <div position="left" title="导航">    '+
    '        <table><tr><td>                    '+
    '        <div id="navigation" ></div>    '+
    '        </td></tr>                        '+
    '        <tr><td>                        '+
    '        <br/>                            '+
    '        <div id="paperBrief" style=" background-color: #FAFAFA; border: 1px solid #DDDDDD;" ></div>'+
    '        </td></tr>                        '+
    '        <tr><td>                        '+
    '        <br/>                            '+
    '        </td></tr>                        '+
    '        </table>                        '+
    '    </div>                                '+
    '    <div position="center" title="标题" ><div type="submit" id="wls_quiz_main" class="w_q_container"></div></div> '+
    '</div> '+
    '');
    $("#layout1").ligerLayout(); 
}  
paperlog.readLog = function(afterAjax){
	var id = getParameter("id", window.location.toString() );
	var paperObj = this;
	$.ajax({
        url : myAppServer() + "&class=education_question_log_wrongs&function=view&id="+id
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
       ,success : function(response) {
    	   var data = response.paperlog;
		   paperlog.cent = data.cent;
		   paperlog.title = data.title;
		   paperlog.id_paper = data.paper_id;
		   paperlog.brief = {
		       subject_name: data.subject_name
		       ,cent: data.cent
		   };
		   
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
               question.path_listen = quesData[i].path_listen;
               question.cent = quesData[i].cent;
               question.id = quesData[i].id;
               question.id_parent = quesData[i].id_parent;
               question.paper = paperlog;
               question.answer = quesData[i].answer;         
               question.myAnswer = quesData[i].myanswer;          
               question.description  = quesData[i].description;                  
               paperlog.questions.push(question);
           }
		   
           paperlog.initLayout();
           paperlog.initQuestions();
           paperlog.initNavigation();
           paperlog.initBrief();
           paperlog.showDescription();
           for(var i=0;i<paperlog.questions.length;i++){
        	   var que = paperlog.questions[i];
        	   que.setMyAnswer();
           }
       }
       ,error : function(){
           $.ligerDialog.error('网络通信失败');
       }
   });
}