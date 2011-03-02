/**
 * Question Type : Check Compare to quetion-singleChoice , It has no options ,
 * only question-title and answer. And two options will be added : right and
 * wrong.
 */
wls.question.check = Ext.extend(wls.question, {

	/**
	 * Add dom to the browser document. After the dom added , the
	 * questionData will be cleared. The questionData is setted in
	 * wls.question
	 */
	initDom : function() {
		$("#wls_quiz_main").append("<div id='w_qs_" + this.id + "'></div>");
		$("#w_qs_" + this.id).append("<div class='w_qw_title'>" + this.index + "&nbsp;<span class='w_qw_tool'></span>"
				+ this.questionData.title + "</div>");
		$("#w_qs_" + this.id).append("<div class='w_qw_options'></div>");
		$(".w_qw_options", "#w_qs_" + this.id).append("<span><input type='radio' onclick='wls_question_done("+this.id+")' name='w_qs_" 
						+ this.id + "' value='A' />&nbsp;" + il8n.right
						+ "</span>");
		$(".w_qw_options", "#w_qs_" + this.id)
				.append("<span><input type='radio' onclick='wls_question_done("+this.id+")' name='w_qs_"
						+ this.id + "' value='B' />&nbsp;" + il8n.wrong
						+ "</span>");

		this.cent = this.questionData.cent;
		this.questionData = null;
	},

	/**
	 * Add dom to browser document. There is a casecading button to
	 * animate the title. The total count of quiz's right , wrong and
	 * giveup will be cumulatived. The answerData is setted in
	 * wls.question
	 */	
	showDescription : function() {
		var obj = this.answerData;
		this.quiz.cent += parseFloat(obj.cent);
		this.quiz.count.total++;
		$(".w_qw_tool", $('#w_qs_' + this.id))
				.append("<a href='#' class='w_q_d_t' title='"
						+ il8n.SeeQuestionAbout + "' id='w_qs_d_t_"
						+ this.id + "' onclick='wls_question_toogle("
						+ this.id
						+ ")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>");
		$('#w_qs_' + this.id).append("<div class='w_q_d'>"
				+ obj.description + "</div>");
		$(".w_q_d_t", $('#w_qs_' + this.id)).addClass("w_q_d_t_2");

		// Give Up
		if (obj.myAnswer == 'I_DONT_KNOW') {
			this.quiz.count.giveup++;
			$(":radio[value='" + obj.answer + "']",
					$("#w_qs_" + this.id)).parent()
					.addClass('w_qs_q_w');
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.giveup + ':' + (this.cent));
			wls_question_toogle(this.id);
			
		// Right
		} else if (obj.answer == obj.myAnswer) {
			this.quiz.count.right++;
			this.quiz.mycent += parseFloat(obj.cent);
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_r');
			$('#w_q_subQuesNav_' + this.id).attr('title',
				il8n.right + ',' + il8n.cent + ':' + (this.cent));
			wls_question_toogle(this.id);
			
		// Wrong	
		} else {
			this.quiz.count.wrong++;
			$(":radio[value='" + obj.answer + "']",
					$("#w_qs_" + this.id)).parent()
					.addClass('w_qs_q_w');
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_w');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.wrong + ':' + (this.cent));
			if (this.quiz.type == 'paper')
				this.addWhyImWrong();
		}
		obj = null;
	},

	/**
	 * This acted befor the quiz was submitted. To avoid the student
	 * submit the paper without doing anything.
	 */	
	getMyAnswer : function() {
		var answer = '';
		var value = $('input[name=w_qs_' + this.id + ']:checked').val();
		if (typeof(value) == 'undefined') {
			answer = 'I_DONT_KNOW';
			this.quiz.count.giveup++;
		} else {
			answer = value;
		}
		return answer;
	}

	/**
	 * This acted when it's in log-review 
	 * TODO There is something wrong
	 * between ExtJS and Jquery.
	 */
	,
	setMyAnswer : function() {
		var myAnswer = this.answerData.myAnswer;
		if (myAnswer != 'I_DONT_KNOW') {
			var temp = {
				A : 0,
				B : 1
			};
			var c = $("input[name=w_qs_" + this.id + "]");
			eval("c[temp." + myAnswer + "].checked = true");
		}
	}
});