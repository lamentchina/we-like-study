wls.question.blank = Ext.extend(wls.question, {

			initDom : function() {
				$("#wls_quiz_main").append("<div id='w_qs_" + this.id
						+ "'></div>");
				$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
						+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
						+ this.questionData.title + "</div>");
				$("#w_qs_" + this.id)
						.append("<span class='w_qw_options'></span>");
				for (var i = 0; i < parseInt(this.questionData.optionlength); i++) {
					eval("var title = this.questionData.option" + (i + 1));
					var str = "<div>" + String.fromCharCode(i + 65)
							+ ":&nbsp;<input type='radio' name='w_qs_"
							+ this.id + "' value='"
							+ String.fromCharCode(i + 65) + "' />&nbsp;"
							+ title;
					if (i != parseInt(this.questionData.optionlength) - 1) {
						str += "</div>";
					}
					$(".w_qw_options", "#w_qs_" + this.id).append(str);
				}
				this.cent = this.questionData.cent;
				this.questionData = null;
			},
			showDescription : function() {
				var obj = this.answerData;
				this.quiz.cent += parseFloat(obj.cent);
				this.quiz.count.total++;
				$(".w_qw_tool", $('#w_qs_' + this.id))
						.append("<a href='#' class='w_q_d_t' title='"
								+ il8n.question + il8n.description
								+ "' id='w_qs_d_t_" + this.id
								+ "' onclick='wls_question_toogle(" + this.id
								+ ")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>");
				$('#w_qs_' + this.id).append("<div class='w_q_d'>"
						+ obj.description + "</div>");
				$(".w_q_d_t", $('#w_qs_' + this.id)).addClass("w_q_d_t_2");

				if (obj.myAnswer == 'I_DONT_KNOW') {
					this.quiz.count.giveup++;
					$(":radio[value='" + obj.answer + "']",
							$("#w_qs_" + this.id)).parent()
							.addClass('w_qs_q_w');

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
					$(":radio[value='" + obj.answer + "']",
							$("#w_qs_" + this.id)).parent()
							.addClass('w_qs_q_w');

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
				var value = $('input[name=w_qs_' + this.id + ']:checked').val();
				if (typeof(value) == 'undefined') {
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
					var temp = {
						A : 0,
						B : 1,
						C : 2,
						D : 3
					};
					var c = $("input[name=w_qs_" + this.id + "]");
					eval("c[temp." + myAnswer + "].checked = true");
				}
			}

		});