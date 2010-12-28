/**
 * 测验卷的一种类型
 * 考试试卷
 * */
var wls_quiz_paper = function(){	
	this.paperId = '';
	this.paperTitle = '';
	
	//TODO
	this.paperData = null;
	this.paperCent = 0;//试卷分数
	this.elapsed_seconds = 0;
	
	this.cent = 0.0;
	this.mycent = 0.0;
	
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
			url: thisObj.config.AJAXPATH+"?controller=quiz_paper&action=getAllQues&id="+thisObj.paperId,
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
	
	//得到这张试卷的所有题目编号和题型
	this.AJAXData = function(nextFunction){

		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_paper&action=getOne&id="+thisObj.paperId,
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				if(obj.ids.length==0){
					$.blockUI({	message: '试卷数据错误!'});
					return;
				}
				thisObj.paperData = obj;
				thisObj.paperId = obj.id;
				thisObj.paperTitle = obj.title;
				$('.w_q_sidebar').append('<div class="w_q_title">'+obj.title+'</div>');
				thisObj.quesId = obj.ids;
				eval(nextFunction);
			}
		});			
	}
	
	this.get_elapsed_time_string = function(total_seconds){
		function pretty_time_string(num) {
			return ( num < 10 ? "0" : "" ) + num;
		}	
		var hours = Math.floor(total_seconds / 3600);
		total_seconds = total_seconds % 3600;
		
		var minutes = Math.floor(total_seconds / 60);
		total_seconds = total_seconds % 60;
		
		var seconds = Math.floor(total_seconds);
		
		  // Pad the minutes and seconds with leading zeros, if required
		hours = pretty_time_string(hours);
		minutes = pretty_time_string(minutes);
		seconds = pretty_time_string(seconds);
		
		  // Compose the string for display
		var currentTimeString = hours + ":" + minutes + ":" + seconds;
		
		return currentTimeString;
	}		
	 
	this.getClock = function(){
		var thisObj = this;
		$('.w_q_sidebar').append("<div class='w_q_p_clock'>时间:<span id='clock'></span></div>");
		setInterval(function() {
			thisObj.elapsed_seconds ++;
		   $('#clock').text(thisObj.get_elapsed_time_string(thisObj.elapsed_seconds));
		}, 1000);
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
					count_right:thisObj.count_right,
					count_giveup:thisObj.count_giveup,
					type:'paper'
					},					
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.getMyMark();				
			}
		});
	}
	
	/**
	 * 在试卷右侧显示我这次测验的分数结果
	 * 来个2秒的闪烁
	 * */
	this.getMyMark = function(){
		$.blockUI({
			 message: '<b>成绩</b>:'+(this.mycent/1000)+'<br/><b>总分</b>:'+(this.cent/1000), 
			 css: { 
	            border: 'none', 
	            padding: '15px', 
	            backgroundColor: '#000', 
	            '-webkit-border-radius': '10px', 
	            '-moz-border-radius': '10px', 
	            opacity: .5, 
	            color: '#fff' 
	        } }); 
		setTimeout($.unblockUI, 2000); 
		var str = "<div class='w_q_p_r'>" +
		"<b>成绩&nbsp;</b>:"+(this.mycent/1000)+"<br/>" +
		"<b>总分&nbsp;</b>:"+(this.cent/1000)+"<br/>" +
		"<b>错题&nbsp;</b>:"+this.count_wrong+"<br/>" +
		"<b>做对&nbsp;</b>:"+this.count_right+"<br/>" +
		"<b>弃题&nbsp;</b>:"+this.count_giveup+"<br/>" +
		"<b>总数&nbsp;</b>:"+this.count_total+"<br/>" +
		"<b>错题率</b>:"+parseInt((this.count_wrong/(this.count_wrong+this.count_right))*100)+"%<br/>";
		if(this.count_manual!=0){
			str += "<b>需批改</b>:"+this.count_manual+"<br/>";
		}
		str += "</div>";
		$('.w_q_sidebar').append(str);		
		$('.w_q_p_clock',$('.w_q_sidebar')).css('display','none');
	}
}
//继承自 wls_quiz
wls_quiz_paper.prototype = new wls_quiz();