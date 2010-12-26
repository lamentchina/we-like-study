/**
 * 单项选择题
 * */
var wls_question_blank = function(){
	
	this.type = 4;
	
	 
	/**
	 * 初始化一个题目
	 * 
	 * @param nextFunction 接下去要执行的函数 
	 * */
	this.initDom = function(nextFunction){	
		
		$(this.targetDom).append("<div id='"+this.quesDomId+"'></div>");
		$("#"+this.quesDomId).append("<div class='w_qw_title'></div>");
		$('#'+this.quesDomId).append("<div id='w_qs__"+this.quesData.id+"'></div>");
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_index'>"+(this.quiz.quesId_sub.length+1)+"</span>");
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_tool'></span>");
		if(this.parentQuestion==null){
			this.quiz.quesId_parsed.push(this.quesData.id);
			this.quiz.quesId_sub.push(this.quesData.id);	
		}else{
			this.quiz.quesId_sub.push(this.quesData.id);	
		}
		
		$(".w_qw_title",$("#"+this.quesDomId)).append(this.quesData.title);
		var class_ = 'w_qs_blank';
		var rows = 1;
		if(this.quesData.details==null || this.quesData.details.lines=='1'){
			class_ += ' w_qs_blank_single';
		}else{
			class_ += ' w_qs_blank_writing';
			rows = this.quesData.details.lines;
		}
		$("#w_qs__"+this.quesData.id).append("<textarea rows='"+rows+"' name='w_qs_"+this.quesData.id+"' class='"+class_+"' /></textarea>");
		if(this.parentQuestion==null){
			this.quiz.quesId_parsed.push(this.quesData.id);
		}
		
		if(this.quiz.type=='paper'){
			this.cent = parseInt(this.quesData.cent*1000);
			this.quiz.cent += this.cent;
			
			this.quiz.mycent += this.cent;
			this.quiz.count_total ++;
		} 
		this.markingmethod = parseInt(this.quesData.markingmethod);
		this.quesData = null;
		eval(nextFunction);
	}
	 
	this.AJAXData = function(nextFunction){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=question_blank&action=getOne&id="+thisObj.quesid+"&index="+thisObj.quesIndex,
			success: function(msg){
				thisObj.quesData = jQuery.parseJSON(msg);
				eval(nextFunction);
			}
		});
	}
	
	this.getMyAnswer = function(){
		var answer = $(':input[name=w_qs_'+this.quesid+']').val();
		if(typeof(answer)=='undefined' || answer==''){
			answer = 'I_DONT_KNOW';
			this.quiz.count_giveup ++;
		}
		return answer;
	}
	
	this.submit = function(nextFunction){
		var thisObj = this;
		
		var answer = this.getMyAnswer();
		
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=question_blank&action=checkone&id="+thisObj.quesid,
			data: {answer:answer},
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.quesData = obj;
				thisObj.description = obj.description;
				$(".w_qw_tool",$('#w_qs_'+thisObj.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+thisObj.quesid+"' onclick='toogleDesc("+thisObj.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
				if(obj.correct=='0'){
					thisObj.answer = obj.answer;
					
					thisObj.wrong();
				}else{
					
				}
				eval(nextFunction);
			}
		});
	}
	
	/**
	 * 这道题做对了
	 * */
	this.right = function(){
		if(this.quiz.type=='paper'){				
			this.quiz.count_right ++;
		} 
		$(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d w_q_d_h'>"+this.description+"</div>");
	}
	
	/**
	 * 这道题做错了
	 * */
	this.wrong = function(){
		 $(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
		if(this.quiz.type=='paper'){
			this.quiz.mycent -= this.cent;					
			this.quiz.count_wrong ++;
		} 
		$('#w_q_subQuesNav_'+this.quesid).addClass('w_q_sn_w');
		$('#w_q_subQuesNav_'+this.quesid).attr('title','做错!\n分值:'+(this.cent/1000));
		$('#w_qs_'+this.quesid).append("<div class='w_q_a'>"+this.answer+"</div>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d'>"+this.description+"</div>");
		$(".w_q_d_t",$('#w_qs_'+this.quesid)).addClass("w_q_d_t_2");
	}
	
	/**
	 * 放弃这道题
	 * */
	this.giveUp = function(){
		 $(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
		if(this.quiz.type=='paper'){
			this.quiz.mycent -= this.cent;
			this.quiz.count_giveup++;
		} 
		$('#w_q_subQuesNav_'+this.quesid).addClass('w_q_sn_g');
		$('#w_q_subQuesNav_'+this.quesid).attr('title','放弃!\n分值:'+(this.cent/1000));
		$('#w_qs_'+this.quesid).append("<div class='w_q_a'>"+this.answer+"</div>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d'>"+this.description+"</div>");
		$(".w_q_d_t",$('#w_qs_'+this.quesid)).addClass("w_q_d_t_2");
	}
	
	/**
	 * 需要人工批改试卷
	 * 只有简答题有这一项,单选,多选,判断都没有
	 * */
	this.needManual = function(){
		if(this.quiz.type=='paper'){
			this.quiz.mycent -= this.cent;
			this.quiz.count_manual ++;			
		} 
		$('#w_q_subQuesNav_'+this.quesid).addClass('w_q_sn_m');
		$('#w_q_subQuesNav_'+this.quesid).attr('title','人工批阅!\n分值:'+(this.cent/1000));
		$('#w_qs_'+this.quesid).append("<div class='w_q_a'>"+this.answer+"</div>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d'>"+this.description+"</div>");
		$(".w_q_d_t",$('#w_qs_'+this.quesid)).addClass("w_q_d_t_2");
	}
}
wls_question_blank.prototype = new wls_question();
