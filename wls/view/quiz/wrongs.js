var wls_quiz_wrongs = function(){
	
	this.type = 'wrongs';

	
	//TODO
	this.paperData = null;
	this.paperCent = 0;//试卷分数
	this.elapsed_seconds = 0;
	
	
	this.setPaperId = function(paperId){
		var thisObj = this;
		thisObj.paperId = paperId;
	}	

	
	this.AJAXAllQues = function(nextFunction){
		 $.blockUI({
			 message: '<h1>正在读取数据...</h1>', 
			 css: { 
	            border: 'none', 
	            padding: '15px', 
	            backgroundColor: '#000', 
	            '-webkit-border-radius': '10px', 
	            '-moz-border-radius': '10px', 
	            opacity: .5, 
	            color: '#fff' 
	        } }); 
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_wrongs&action=getMyWrongs",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				
				for(var i=0;i<obj.length;i++){
					var ques = null;
					switch(obj[i].type){
						case '5':
							ques = new wls_question_reading();
							ques.quesData = obj[i];
							break;
						case '4':
							ques = new wls_question_blank();
							ques.quesData = obj[i];
							break;	
						case '1':
							ques = new wls_question_choice();
							ques.quesData = obj[i];
							break;								
						default :
							break;
					}
					ques.quiz = thisObj;
					ques.config = thisObj.config;
					ques.quesDomId = 'w_qs_'+obj[i].id;
					ques.quesid = obj[i].id;
					ques.targetDom = $('.w_q_container',$('#'+thisObj.quizDomId));

					thisObj.questions.push(ques);
					ques.initDom(null);
				}

				eval(nextFunction);
				$.unblockUI();
			}
		});		
	}	
	
	this.submitPaper = function(){
		var thisObj = this;
		$.ajax({
			url: this.config.AJAXPATH+"?controller=quiz_record&action=add",
			data: {	id:thisObj.paperId,
					cent:(thisObj.cent/1000),
					mycent:(thisObj.mycent/1000),
					timer:thisObj.elapsed_seconds,
					title:$('.w_q_title').text(),
					count_wrong:thisObj.count_wrong,
					count_total:thisObj.count_total,
					type:'paper'
					},
					
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.getMyMark();
				
			}
		});
	}
}
wls_quiz_wrongs.prototype = new wls_quiz();