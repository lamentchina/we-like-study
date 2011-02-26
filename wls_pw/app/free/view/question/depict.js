wls.question.depict = Ext.extend(wls.question, {
			initDom : function() {
	
				$("#wls_quiz_main").append("<div id='w_qs_" + this.id
						+ "'></div>");
				$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
						+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
						+ this.questionData.title + "<br/><br/><textarea rows='10' style='width:90%;height:150px;' name='w_qs_"+this.id+"' ></textarea></div>");
				this.cent = this.questionData.cent;
				this.questionData = null;
				
			},
			showDescription : function() {
				var obj = this.answerData;
				this.quiz.cent += parseFloat(obj.cent);
				this.quiz.count.total++;		

			},
			getMyAnswer : function() {

				var answer = '';
				var value = $('textarea[name=w_qs_' + this.id + ']').val();
				if (typeof(value) == 'undefined' || value=='') {
					answer = 'I_DONT_KNOW';
					this.quiz.count.giveup++;
				} else {
					answer = value;
				}
				return answer;

			},
			setMyAnswer : function() {
				var myAnswer = this.answerData.myAnswer;
				if (myAnswer != 'I_DONT_KNOW') {
					var c = $("textarea[name=w_qs_" + this.id + "]");
					c.attr("value",myAnswer);
				}

			}
		});