/**
 * 单项选择题
 * 只能有一个正确答案,可以有多个备选答案
 * 有一行题目描述.
 * 答案的布局,可以纵向排列(一个选项一行),
 * 也可以横向排列(所有选项都一排),比如在完型填空中,就需要用 横向排列
 * */
wls.question.choice = Ext.extend(wls.question, {
	
	optionLength : 4, //单项选择题中的备选的答案个数,默认为 4,就是 A B C D四个选项
	options : [],     //选项的具体内容,一个包含一堆字符串的数组,可能会有一个空元素(因此需要 opentionLength 字段描述真实长度)
	
	/**
	 * 初始化页面控件
	 * 住页面必须有这个元素: <div id = 'wls_quiz_main'>
	 * */
	initDom : function() {
		$("#wls_quiz_main").append("<div id='w_qs_" + this.id + "'></div>");
		
		//备选答案是 水平排列的,比如完型填空题中的选择题,使用 SPAN 标签
		if(this.layout == 'horizontal'){
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.id + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.title + "</div>");
			
			//为了确保每个选项都是等长,使用 TABLE 标签控制
			$("#w_qs_" + this.id).append("<span class='w_qw_options'></span>");			
			var str = "<table width='90%'><tr>";					
			for (var i = 0; i < parseInt(this.optionLength); i++) {
				var optionStr = "<td width='"+parseInt(100/this.optionLength)+"%'>" 
									+ String.fromCharCode(i + 65) // A B C D ...
									+ ":&nbsp;<input type='radio' "
									+ " onclick='wls_question_done("+this.id+")' " //选项点击之后,改变颜色,并设置 选择答案
									+ " name='w_qs_" + this.id + "' value='" + String.fromCharCode(i + 65) + "' />&nbsp;" 
									+ this.options[i]+"</td>";
									
				str += optionStr;
			}					
			str += "</tr></table>";
			$(".w_qw_options", "#w_qs_" + this.id).append(str);
			
		}else{
			//如果题目是纵向排列的,就用DIV标签
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.id + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.title + "</div>");
			$("#w_qs_" + this.id).append("<span class='w_qw_options'></span>");
			
			for (var i = 0; i < parseInt(this.optionLength); i++) {
				var str = "<div>" + String.fromCharCode(i + 65) // A B C D
						+ ":&nbsp;<input type='radio' "
						+ " onclick='wls_question_done("+this.id+")' name='w_qs_" //选项点击之后,改变颜色,并设置 选择答案
						+ this.id + "' value='"
						+ String.fromCharCode(i + 65) + "' />&nbsp;"
						+ this.options[i];
				if (i != parseInt(this.optionLength) - 1) {
					str += "</div>";
				}
				$(".w_qw_options", "#w_qs_" + this.id).append(str);
			}
		}
	},
	
	/**
	 * 提交答案,
	 * */
	submit : function(){
	
		var value = $('input[name=w_qs_' + this.id + ']:checked').val();
		if (typeof(value) == 'undefined') {
			this.myAnswer = 'I_DONT_KNOW';
			this.quiz.count.giveup++;
		} else {
			this.myAnswer = value;
			
			if(this.myAnswer==this.answer){//做对了
				
			}else{//做错了
				
			}
		}
		if(this.state != 'submitted'){
			$('#w_qs_' + this.id).append("<div class='w_q_d'>"+ this.description + "</div>");
		}
		this.state = 'submitted';
	},
	
	/**
	 * 当用户做完一张卷子,点击 提交 之后,程序会逐一判断用户有没有作对
	 * 如果做对了,题目导航处变蓝,题目处影藏解题思路
	 *     做错了,题目导航处变红,题目处显示解题思路
	 *     弃权了,题目导航处变黄,题目处影藏解题思路
	 * */
	showDescription : function() {
		/*
		var obj = this.answerData;
		this.quiz.cent += parseFloat(obj.cent);
		this.quiz.count.total++;

		if(obj.description!='nothing'){
			$(".w_qw_tool", $('#w_qs_' + this.id))
			.append("<a href='#' class='w_q_d_t' title='"
					+ il8n.quiz.question + il8n.normal.description
					+ "' id='w_qs_d_t_" + this.id
					+ "' onclick='wls_question_toogle(" + this.id
					+ ")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>");
			$('#w_qs_' + this.id).append("<div class='w_q_d'>"+ obj.description + "</div>");
		}
		$(".w_q_d_t", $('#w_qs_' + this.id)).addClass("w_q_d_t_2");

		//弃权
		if (obj.myAnswer == 'I_DONT_KNOW') {
			this.quiz.count.giveup++;
			$(":radio[value='" + obj.answer + "']",
					$("#w_qs_" + this.id)).parent()
					.addClass('w_qs_q_w');

			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.quiz.giveup + ',' + il8n.quiz.cent + ':' + (this.cent));
			wls_question_toogle(this.id);
			
		//作对了
		} else if (obj.answer == obj.myAnswer) {
			this.quiz.count.right++;
			this.quiz.mycent += parseFloat(obj.cent);
			
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_r');
			$('#w_q_subQuesNav_' + this.id).attr('title',
				il8n.quiz.right + ',' + il8n.quiz.cent + ':' + (this.cent));
			wls_question_toogle(this.id);
		
		//做错了
		} else {
			this.quiz.count.wrong++;
			$(":radio[value='" + obj.answer + "']",
					$("#w_qs_" + this.id)).parent()
					.addClass('w_qs_q_w');

			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_w');
			$('#w_q_subQuesNav_' + this.id).attr('title',
				il8n.quiz.wrong + ',' + il8n.quiz.cent + ':' + (this.cent));
			if (this.quiz.type == 'paper'){
				this.addWhyImWrong();
			}
		}
		
		obj = null;
		*/
	},
	
	/**
	 * When the paper was submitted , this function will be called
	 * */
	getMyAnswer : function() {

		var answer = '';
		var value = $('input[name=w_qs_' + this.id + ']:checked').val();
		if (typeof(value) == 'undefined') {
			answer = 'I_DONT_KNOW';
			this.quiz.count.giveup++;
		} else {
			answer = value;
		}
		
		if(this.quiz.containAnswer){
			this.answerData.myAnswer = answer;
			//console.debug(this.answerData);
			this.showDescription();
		}
		
		return answer;
	},
	
	/**
	 * It's used 
	 * when the user want to review the quizs which he has done befor
	 * */
	setMyAnswer : function() {
		var myAnswer = this.answerData.myAnswer;

		if (myAnswer != 'I_DONT_KNOW') {
			var temp = {
				A : 0,
				B : 1,
				C : 2,
				D : 3,
				E : 4
			};
			var c = $("input[name=w_qs_" + this.id + "]");

			eval("c[temp." + myAnswer + "].checked = true");
		}
	}
});