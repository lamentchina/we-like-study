/**
 * 单项选择题
 * */
var wls_question_choice = function(){
	
	this.type = 1;
	 
	/**
	 * 初始化一个题目
	 * 
	 * @param nextFunction 接下去要执行的函数 
	 * */
	this.initDom = function(nextFunction){	
		$(this.targetDom).append("<div id='"+this.quesDomId+"'></div>");
		//如果是完形填空题,则标题为空,听力题的标题为&nbsp;
		if(this.quesData.title==''){
			$("#"+this.quesDomId).append("<span class='w_qw_title'></span>");
			$('#'+this.quesDomId).append("<span id='w_qs__"+this.quesData.id+"'></span>");
		}else{
			$("#"+this.quesDomId).append("<div class='w_qw_title'></div>");
			$('#'+this.quesDomId).append("<div id='w_qs__"+this.quesData.id+"'></div>");
		}
		
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_index'>"+(this.quiz.quesId_sub.length+1)+"</span>");
		$(".w_qw_title",$("#"+this.quesDomId)).append("<span class='w_qw_tool'></span>");
		if(this.parentQuestion==null){
			this.quiz.quesId_parsed.push(this.quesData.id);
			this.quiz.quesId_sub.push(this.quesData.id);	
		}else{
			this.quiz.quesId_sub.push(this.quesData.id);	
		}
		$(".w_qw_title",$("#"+this.quesDomId)).append(this.quesData.title);

		if(this.quesData.details!=null){
			if(typeof(this.quesData.details.display)=='string' && this.quesData.details.display == "vertical"){
				var str = "";
				for(var i=0;i<this.quesData.details.options.length;i++){
					str += "<span style='width:300px;' >";
					str += "<input type='radio' name='w_qs_"+this.quesData.id+"' value='"+this.quesData.details.options[i].option+"'/>";
					str += this.quesData.details.options[i].option+':'+this.quesData.details.options[i].title+'&nbsp;';
					str += "</span>";
				}
				$("#w_qs__"+this.quesData.id).append(str);
			}else{
				for(var i=0;i<this.quesData.details.options.length;i++){
					$("#w_qs__"+this.quesData.id).append("<div class='w_q_option'><input type='radio' name='w_qs_"+this.quesData.id+"' value='"+this.quesData.details.options[i].option+"' />"+this.quesData.details.options[i].option+':'+this.quesData.details.options[i].title+"</div>");
				}
			}
		}
		if(this.parentQuestion==null)this.quiz.quesId_parsed.push(this.quesData.id);
		
		if(this.quiz.type=='paper'){
			this.cent = parseInt(this.quesData.cent*1000);
			this.quiz.cent += this.cent;

			this.quiz.mycent += this.cent;
			this.quiz.count_total ++;
		} 
		this.quesData = null;
		eval(nextFunction);
	}
	 
	this.AJAXData = function(nextFunction){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=question_choice&action=getOne&id="+thisObj.quesid+"&index="+thisObj.quesIndex,
			success: function(msg){
				thisObj.quesData = jQuery.parseJSON(msg);
				eval(nextFunction);
			}
		});
	}
	
	this.getMyAnswer = function(){
		var answer = '';
		var value = $('input[name=w_qs_'+this.quesid+']:checked').val();
		if(typeof(value)=='undefined'){
			answer = 'I_DONT_KNOW';
			this.quiz.count_giveup ++;
		}else{
			answer = value;
		}
		return answer;
	}
	
	this.submit = function(nextFunction){
		var thisObj = this;
		
		var answer = this.getMyAnswer();
		
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=question_choice&action=checkone&id="+thisObj.quesid,
			data: {answer:answer},
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.quesData = obj;
				$(".w_qw_tool",$('#w_qs_'+thisObj.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+thisObj.quesid+"' onclick='toogleDesc("+thisObj.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
				
				if(obj.correct=='1'){
					$('#w_qs_'+thisObj.quesid).append("<div class='w_q_d w_q_d_h'>"+obj.description+"</div>");
					
					
				}else{
					if(thisObj.quiz.type=='paper'){
						thisObj.quiz.mycent -= thisObj.cent;
						thisObj.quiz.count_wrong ++;
					} 			
					
					if(obj.correct=='0'){
						$('#w_q_subQuesNav_'+thisObj.quesid).addClass('w_q_sn_w');
					}else if(obj.correct=='2'){
						$('#w_q_subQuesNav_'+thisObj.quesid).addClass('w_q_sn_g');
					}					
					$('#w_q_subQuesNav_'+thisObj.quesid).attr('title','分值:'+thisObj.cent);
					$('#w_qs_'+thisObj.quesid).append("<div class='w_q_d'>"+obj.description+"</div>");
					$(".w_q_d_t",$('#w_qs_'+thisObj.quesid)).addClass("w_q_d_t_2");

					
					$(":radio[value='"+obj.answer+"']", $("#w_qs__"+thisObj.quesid)).parent().addClass('w_qs_q_w');
				}
				eval(nextFunction);
			}
		});
	}
	

	this.right = function(){
		if(this.quiz.type=='paper'){				
			this.quiz.count_right ++;
		} 
		$(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d w_q_d_h'>"+this.description+"</div>");
	}
	
	this.wrong = function(){
		$(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");

		if(this.quiz.type=='paper'){
			this.quiz.mycent -= this.cent;						
			this.quiz.count_wrong ++;	
		} 
		$('#w_q_subQuesNav_'+this.quesid).addClass('w_q_sn_w');
		$('#w_q_subQuesNav_'+this.quesid).attr('title','做错\n分值:'+(this.cent/1000));
		$('#w_qs_'+this.quesid).append("<div class='w_q_a'>"+this.answer+"</div>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d'>"+this.description+"</div>");
		$(".w_q_d_t",$('#w_qs_'+this.quesid)).addClass("w_q_d_t_2");
		$(":radio[value='"+this.answer+"']", $("#w_qs__"+this.quesid)).parent().addClass('w_qs_q_w');
	}
	
	this.giveUp = function(){
		$(".w_qw_tool",$('#w_qs_'+this.quesid)).append("<a href='#' class='w_q_d_t' title='查看解题思路及说明' id='w_qs_d_t_"+this.quesid+"' onclick='toogleDesc("+this.quesid+")'>&nbsp;&nbsp;&nbsp;</a>");

		if(this.quiz.type=='paper'){
			this.quiz.mycent -= this.cent;
			this.quiz.count_giveup ++;
		} 
		$('#w_q_subQuesNav_'+this.quesid).addClass('w_q_sn_g');
		$('#w_q_subQuesNav_'+this.quesid).attr('title','放弃\n分值:'+(this.cent/1000));
		$('#w_qs_'+this.quesid).append("<div class='w_q_a'>"+this.answer+"</div>");
		$('#w_qs_'+this.quesid).append("<div class='w_q_d'>"+this.description+"</div>");
		$(".w_q_d_t",$('#w_qs_'+this.quesid)).addClass("w_q_d_t_2");
		$(":radio[value='"+this.answer+"']", $("#w_qs__"+this.quesid)).parent().addClass('w_qs_q_w');
	}
	
}
wls_question_choice.prototype = new wls_question();
