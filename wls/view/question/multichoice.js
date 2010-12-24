/**
 * 单项选择题
 * */
var wls_question_type_multichoice = function(naming,wls_question){
	var thisObj = this;
	this.w_q = wls_question;
	
	this.naming = naming;
	
	this.quesData = null;
	this.domid = null;
	
	//一个阅读理解题提交之后,后台返回的数据,包含了众多子题目的解题说明
	this.responseData = null;
	
	/**
	 * 初始化一个阅读理解题目
	 * 
	 * @param id 主题目编号
	 * @param nextFunction 接下去要执行的函数 
	 * @param index 上一道题目的顺序编号
	 * */
	this.initOne = function(id,nextFunction,index,domid){
		thisObj.domid = domid; 
		$.ajax({
			url: "/index2.php?option=com_wls&controller=question_type_multichoice&action=getOne&no_html=1&id="+id+"&index="+index,
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.quesData = obj;

				$('#'+domid).append("<div id='question_"+obj.id+"'>"+obj.title+"</div>");
				for(var j1=0;j1<obj.details.options.length;j1++){
					$("#question_"+obj.id).append("<div class='options'><input type='checkbox' name='w_q_t_ch_r_"+obj.id+"' value='"+obj.details.options[j1].option+"' />"+obj.details.options[j1].option+':'+obj.details.options[j1].title+"</div>");
				}
			}
		});
	}
	
	this.initButton = function(){

		$("#w_q_t_ch_btn").append('<div>1242134</div>');

		//$("#w_q_t_ch_btn").append("<button onclick='"+thisObj.naming+".submit()' id='w_q_t_ch_b_s' >提交</div>");
		//$("#w_q_t_ch_btn").append("<button onclick='"+thisObj.naming+".next()' >下一题</div>");		
	};
	
	/**
	 * 提交一道 阅读理解 题
	 * 将向后台提交: 
	 *   母题目的编号,即阅读理解题目的编号
	 *   子题目的编号集,即每个选择题的编号
	 *   每道题目的答案.如果用户没有选择题目,则以I_DONT_KNOW发送
	 * 如果题目做错了的话
	 *   高亮显示题目,并显示答案和解题思路
	 *   提供一个题目做错评论框
	 * */
	this.submit = function(){
		var value = $("input[name='w_q_t_ch_r_"+thisObj.quesData.id+"']:checked");
		if(value.length==0){
			return;
		}
		var answers = '';
		for(var i=0;i<value.length;i++){
			answers += $(value[i]).val()+',';
		}
		answers = answers.substr(0,answers.length-1);		

		$.ajax({
			url: "/index2.php?no_html=1&option=com_wls&controller=question_type_multichoice&action=checkOne",
			data: {id:thisObj.quesData.id,answer:answers},
			type: "POST",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				thisObj.responseData = obj;
				if(obj.result==0){
					thisObj.showDescription(thisObj.quesData.id);
					thisObj.w_q.embedYWrong(thisObj.quesData.id);
					thisObj.w_q.embedRating(thisObj.quesData.id);
				}
			}
		});
	}
	
	/**
	 * 显示一道题目的解题说明
	 * @param id 自题目的编号
	 * */
	this.showDescription = function(id){

		 var arr = thisObj.responseData.question.answer.split(',');
		 for(var i=0;i<arr.length;i++){
			 $("input[value="+arr[i]+"]",$("#question_"+id)).parent().addClass('w_q_t_mc_sc_w');
		 }
		 $("#question_"+id).append("<div id='w_q_desc_"+id+"' class='w_q_d'>"+thisObj.responseData.question.description+"</div>");
	}
	
	/**
	 * 随机产生一道题目
	 * */
	this.next = function(){
		$("#"+thisObj.domid).empty();
		//$("#w_q_t_ch_btn").empty();
		thisObj.initOne(0,null,0,thisObj.domid);
	}
}

