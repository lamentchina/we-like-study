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
 * 只有学生才能操作错题本,教师,管理员都无法操作
 * 可以看到的列以及能够执行的操作有:
 *   题目标题,科目,创建时间,试卷标题
 *   查询,导出,练习
 *   
 * @version 201212
 * @author wei1224hf@gmail.com
 * */
var education_question_log_wrongs = {
	        
	ajaxState: false        
	
	,grid : function(){
    	var gridColmuns = [
    	   [//管理员列
    	   
    	   ],[//学生列
  	         { display: top.il8n.education_question_log_wrongs.question_title, name: 'question_title', align: 'left', width: 140 }
  	        ,{ display: top.il8n.education_question_log_wrongs.paper_title, name: 'paper_title', align: 'left', width: 140 }
  	        ,{ display: top.il8n.education_question_log_wrongs.count_wrong, name: 'count_wrong', align: 'left' }
  	        ,{ display: top.il8n.education_question_log_wrongs.count_right, name: 'count_right', align: 'left' }
  	        ,{ display: top.il8n.education_question_log_wrongs.subject_name, name: 'subject_name',isSort : false }
  	        ,{ display: top.il8n.education_question_log_wrongs.subject_code, name: 'subject_code',isSort : false, hide: true }
  	        ,{ display: top.il8n.time_created, name: 'time_created', width: 80 } 
           ],[//教师列

           ]
        ];
		
        var config = {
            height:'100%'
            ,columns: []
            ,pageSize:20 
            ,rownumbers:true
            ,url : myAppServer()+ "&class=education_question_log_wrongs&function=grid"
            ,method  : "POST"
            ,id : "education_question_log_wrongs__grid"
            ,parms : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code               
            }
            ,toolbar: { items: [] }
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
        
		var permission = top.basic_user.permission;
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='14'){
				permission = permission[i].children;
				break;
			}
		}
		for(var i=0;i<permission.length;i++){
			if(permission[i].code=='1402'){
				permission = permission[i].children;
				break;
			}
		}		
		
        for(var i=0;i<permission.length;i++){
            if(permission[i].code=='140201'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
                        education_question_log_wrongs.search();
                    },disable:true
                });
            }else if(permission[i].code=='140290'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){

                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_wrongsbook_log" , height:  550, width: 600,                            
                            url: "education_question_log_wrongs__do.html",
                            showMax: true, showToggle: true, showMin: true, isResize: true,
                            modal: false, 
                            slide: false    
                        }).max();
                        
                        top.$.ligerui.get("win_wrongsbook_log").close = function(){
                            var g = this, p = this.options;
                            top.$.ligerui.win.removeTask(this);
                            g.unmask();
                            g._removeDialog();
                            top.$.ligerui.remove(top.$.ligerui.get("win_wrongsbook_log"));
                            top.$('body').unbind('keydown.dialog');
                        }
                    }
                });
            }else if(permission[i].code=='140212'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
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
                        $.ligerui.get("education_question_log_wrongs__grid").options.parms.search =  $.ligerui.toJSON({foo:1});
                        $.ligerui.get("education_question_log_wrongs__grid").loadData();
						
						$.ligerui.get("title").setValue('');
						$.ligerui.get("combo_select").setValue('');
					}},
					//提交查询条件
				    {text:top.il8n.search,onclick:function(){
				    	$.ligerui.get("education_question_log_wrongs__grid").options.parms.search =  $.ligerui.toJSON({					
							title : $.ligerui.get("title").getValue(),
							subject : $.ligerui.get("combo_select").getValue(),
							foo : 'bar'
						});					
				    	$.ligerui.get("education_question_log_wrongs__grid").loadData();
				    }}
				]
			});
		}
	}	

	,do_: function(){
		paper.readQuestions = function(afterAjax){
	        $.ajax({
	            url : myAppServer()+ "&class=education_question_log_wrongs&function=getForPaper",
	            type : "POST",
	            data : {
	                 username: top.basic_user.username
	                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
	                ,search: $.ligerui.toJSON( basic_user.searchOptions )
	                ,user_id: top.basic_user.loginData.id
	                ,user_type: top.basic_user.loginData.type    
	                ,group_id: top.basic_user.loginData.group_id
	                ,group_code: top.basic_user.loginData.group_code   
	            },
	            dataType: 'json',
	            success : function(responseData) {
	                paper.questions = responseData.Rows;
	             	                
	                if ( typeof(afterAjax) == "string" ){
		                eval(afterAjax);
		            }else if( typeof(afterAjax) == "function"){
		                afterAjax();
		            }	
	            },
	            error : function(){
	                $.ligerDialog.error('网络通信失败');
	            }
	        });
		}
		
		paper.submit = function(){
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
	        var paperObj = this;
	        
	        $.ajax({
	            url : myAppServer()+ "&class=education_question_log_wrongs&function=submit",
	            type : 'POST',
	            data : {
	                 json: $.ligerui.toJSON(toSend)
	                ,id: paperObj.id_paper
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
	                var questions = data.questions;
	                //console.debug(questions.length);
	                for(var i=0;i < questions.length;i++){
	                    //console.debug(questions[i]+" "+i);
	                	paperObj.questions[i].answer = questions[i].answer;
	                	paperObj.questions[i].description = questions[i].description;
	                    
	                }                
	                paperObj.showDescription();
	                
	                
	                $('#submit').remove();
	            }
	        });
		}
		
		paper.readQuestions("paper.initLayout();paper.initQuestions();paper.initNavigation();");
	}
};