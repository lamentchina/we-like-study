wls.question.big = Ext.extend(wls.question, {
			initDom : function() {
				$("#wls_quiz_main").append("<div id='w_qs_" + this.id
						+ "'></div>");
				$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
						+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
						+ this.questionData.title + "</div>");
				this.cent = this.questionData.cent;
				this.questionData = null;
			},
			showDescription : function() {

			},
			getMyAnswer : function() {
				return '';
			},
			setMyAnser : function() {

			}
		});