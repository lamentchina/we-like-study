/**
 * WLS,We-Like-Study,An online examination application
 * The single-choice question type.
 * 
 * @author wei1224hf
 * */
wls.question.choice = Ext.extend(wls.question, {
	

	initDom : function() {
		$("#wls_quiz_main").append("<div id='w_qs_" + this.id + "'></div>");
		
		//To display the options horizontally, use table-tag
		if(parseInt(this.questionData.layout)==0){
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.questionData.title + "</div>");
			
			//Add the options in a table, the table is inside a span-tag
			$("#w_qs_" + this.id).append("<span class='w_qw_options'></span>");			
			var str = "<table width='90%'><tr>";					
			for (var i = 0; i < parseInt(this.questionData.optionlength); i++) {
				eval("var title = this.questionData.option" + (i + 1));
				var optionStr = "<td width='"+parseInt(100/this.questionData.optionlength)+"%'>" 
									+ String.fromCharCode(i + 65) //Option Index: A B C D ...
									+ ":&nbsp;<input type='radio' "
									+ " onclick='wls_question_done("+this.id+")' " //Change the navigater item color
									+ " name='w_qs_" + this.id + "' value='" + String.fromCharCode(i + 65) + "' />&nbsp;" 
									+ title+"</td>";
									
				str += optionStr;
			}					
			str += "</tr></table>";
			$(".w_qw_options", "#w_qs_" + this.id).append(str);
			
		}else{
			//To display the options vertically, use div-tag
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.questionData.title + "</div>");
			$("#w_qs_" + this.id).append("<span class='w_qw_options'></span>");
			
			for (var i = 0; i < parseInt(this.questionData.optionlength); i++) {
				eval("var title = this.questionData.option" + (i + 1));
				var str = "<div>" + String.fromCharCode(i + 65) //Option Index: A B C D
						+ ":&nbsp;<input type='radio' "
						+ " onclick='wls_question_done("+this.id+")' name='w_qs_" //Event handler , Change the navigater item color
						+ this.id + "' value='"
						+ String.fromCharCode(i + 65) + "' />&nbsp;"
						+ title;
				if (i != parseInt(this.questionData.optionlength) - 1) {
					str += "</div>";
				}
				$(".w_qw_options", "#w_qs_" + this.id).append(str);
			}
		}

		this.cent = this.questionData.cent;
		this.questionData = null;
	},
	
	marking:function(){
		var c = $("input[name=w_qs_" + this.id + "]");
		for(var i=0;i<c.length;i++){
			$(c[i]).attr('disabled',true);;
		}
		if(this.markingmethod!=0){
			$('#w_q_subQuesNav_' + this.id).attr('class','w_q_sn_mark');
			$(".w_qw_tool", $('#w_qs_' + this.id)).append("<a href='#' onclick='wls_question_marking("+this.id+","+this.cent+","+this.id+")'>"+il8n.quiz.marking+"</a>");
		}
	},
	
	/**
	 *  When the paper was submitted , cilent send the myAnswers to the server
	 * ,the server caculate the mark, and send the current answers back to client
	 * ,the client should display the current answer and tell the user 
	 * 		'What you've done were right and what were wrong'
	 * 
	 * Do the question right:
	 * 		Navigater Item: Blue
	 * 		Hidde the description
	 * Do the question wrong:
	 * 		Navigater Item: Red
	 * 		Display the description
	 * Give Up the question:
	 * 		Navigater Item: Yellow
	 * 		Hidde the description
	 * */
	showDescription : function() {
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

		
		if(obj.markingmethod==1){
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_mark');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.quiz.markingByTeacher + ',' + il8n.quiz.cent + ':' + (this.cent));

			if(this.quiz.type=='log'){
				$(".w_qw_tool", $('#w_qs_' + this.id)).append("<a href='#' onclick='wls_question_markked("+this.quiz.logData.id+","+this.id+")'>"+il8n.quiz.marking+"</a>");
			}
		}else{			
			//Give Up
			if (obj.myAnswer == 'I_DONT_KNOW') {
				this.quiz.count.giveup++;
				$(":radio[value='" + obj.answer + "']",
						$("#w_qs_" + this.id)).parent()
						.addClass('w_qs_q_w');
	
				$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
				$('#w_q_subQuesNav_' + this.id).attr('title',
						il8n.quiz.giveup + ',' + il8n.quiz.cent + ':' + (this.cent));
				wls_question_toogle(this.id);
				
			//Right
			} else if (obj.answer == obj.myAnswer) {
				this.quiz.count.right++;
				this.quiz.mycent += parseFloat(obj.cent);
				
				$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_r');
				$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.quiz.right + ',' + il8n.quiz.cent + ':' + (this.cent));
				wls_question_toogle(this.id);
			
			//Wrong
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
		}
		obj = null;
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