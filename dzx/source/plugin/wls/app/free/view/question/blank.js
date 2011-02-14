wls.question.blank = Ext.extend(wls.question, {
	initDom : function() {
		if(this.questionData.id_parent==0){
			$("#wls_quiz_main").append("<div id='w_qs_" + this.id
					+ "'></div>");
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.questionData.title + "</div>");
		}else{
			$("[index="+this.questionData.title+"]",$("#w_qs_"+this.questionData.id_parent)).attr("id","w_qs_"+this.id);
			$("[index="+this.questionData.title+"]",$("#w_qs_"+this.questionData.id_parent)).attr("name","w_qs_"+this.id);
		}
		this.cent = this.questionData.cent;
		this.questionData = null;
	},
	showDescription : function() {
		var obj = this.answerData;
		this.quiz.cent += parseFloat(obj.cent);
		this.quiz.count.total++;		
		 $('#w_qs_' + this.id).attr("title",obj.answer+" "+obj.description);
		if (obj.myAnswer == 'I_DONT_KNOW') {
			this.quiz.count.giveup++;
			 $('#w_qs_' + this.id).attr("value",obj.answer);
			$('#w_qs_' + this.id).attr("class","w_blank_dontknow");
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.cent + ':' + (this.cent));
			wls_question_toogle(this.id);
		} else if (obj.answer == obj.myAnswer) {			
			this.quiz.count.right++;
			this.quiz.mycent += parseFloat(obj.cent);
			wls_question_toogle(this.id);
		} else {
			this.quiz.count.wrong++;
			$('#w_qs_' + this.id).attr("class","w_blank_wrong");
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_w');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.cent + ':' + (this.cent));
			if (this.quiz.type == 'paper')
				this.addWhyImWrong();
		}

		obj = null;
	},
	getMyAnswer : function() {
		var answer = '';
		var value = $('input[name=w_qs_' + this.id + ']').val();
		if (typeof(value) == 'undefined' || value=='') {
			answer = 'I_DONT_KNOW';
			this.quiz.count.giveup++;
		} else {
			answer = value;
		}
		return answer;

	},
	setMyAnser : function() {
		var myAnswer = this.answerData.myAnswer;
		if (myAnswer != 'I_DONT_KNOW') {
			var c = $("input[name=w_qs_" + this.id + "]");
			c.attr("value",myAnswer);
		}
	}

});