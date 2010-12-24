var wls_question_reading = function(){
	
	this.type = 5;
	
	/**
	 * 子题目,如果有自题目的话
	 * */
	this.childQuestions = [];
	
	/**
	 * 初始化一个题目
	 * 
	 * @param nextFunction 接下去要执行的函数 
	 * */
	this.initDom = function(nextFunction){		
		
		$(this.targetDom).append("<div id='"+this.quesDomId+"'></div>");

		$("#"+this.quesDomId).append("<div class='w_qw_title'></div>");
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_index'>"+(this.quiz.quesId_parsed.length+1)+"</span>");
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_tool'></span>");
		if(this.parentQuestion==null)this.quiz.quesId_parsed.push(this.quesData.id);
		
		//如果含有听力
		var thisObj = this;

		if(this.quesData.details!=null && typeof(this.quesData.details)=='object' && typeof(this.quesData.details.listen)=='string'){
			$(".w_qw_tool",$(".w_qw_title",$("#"+this.quesDomId))).append("<span class='mp3'>"+this.quesData.details.listen+"</span>");
			$('.mp3',$(".w_qw_tool",$(".w_qw_title",$("#"+this.quesDomId)))).jmp3({
				filepath: thisObj.config.MP3PATH,
				backcolor: "E7EFF0",
				forecolor: "8B4513",
				width: 75,
				height: 50,
				showdownload: "false",
				showfilename: "false"
			});
		}
		
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span>"+this.quesData.title+"</span>");		
		$('#'+this.quesDomId).append("<div id='w_qs_"+this.quesData.id+"'></div>");

		for(var i=0;i<this.quesData.child.length;i++){
			var ques = null;
			if(this.quesData.child[i].type=='1'){
				ques = new wls_question_choice();
			}else if(this.quesData.child[i].type=='4'){
				ques = new wls_question_blank();
			}
			ques.quesDomId = 'w_qs_'+this.quesData.child[i].id;
			ques.quesid = this.quesData.child[i].id;
			ques.targetDom = $('#w_qs_'+this.quesData.id);
			ques.quesData = this.quesData.child[i];
			ques.config = this.config;
			ques.parentQuestion = this;
			ques.quiz = this.quiz;
			
			ques.initDom(null);
			this.childQuestions.push(ques);
		}
		
		this.quesData = null;
		eval(nextFunction);
	}
	 
	this.AJAXData = function(nextFunction){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=question_reading&action=getOne&id="+thisObj.quesid+"&index="+thisObj.quesIndex,
			success: function(msg){
				thisObj.quesData = jQuery.parseJSON(msg);
				eval(nextFunction);
			}
		});
	}
	
	//TODO
	this.submit = function(index,nextFunction){
		console.debug('r_s');
		if(index==0){//依次提交子题目

		}else if(index==this.childQuestions.length){//子题目提交完成

			return;
		}else{
			this.parseQuesList(index-1,null);
		}
		var funstr = this.quiz.naming+".quesListAJAXData("+(index+1)+",'"+nextFunction+"');";
		this.childQuestions[index].submit(funstr);
	}
}
wls_question_reading.prototype = new wls_question();
