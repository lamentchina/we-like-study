wls.quiz.random = Ext.extend(wls.quiz, {
	subject_id_level:null,
	questionType:null,
	type : 'random',
	ajaxIds : function(nextFunction) {
		var thisObj = this;
		$.blockUI({message : '<h1>' + il8n.loading + '</h1>'});
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=quiz_random&action=getOne",

			success : function(response) {
				thisObj.ids_questions = response.responseText;
				thisObj.state = 1;
				$.unblockUI();
				eval(nextFunction);
			},
			params : {subject_id_level:thisObj.subject_id_level,
					questionType:thisObj.questionType}
		});
	},
	submit : function(nextFunction) {
		$.blockUI({message : '<h1>' + il8n.loading + '</h1>'});
		this.answersData = [];
		for (var i = 0; i < this.questions.length; i++) {
			this.answersData.push({
				id : this.questions[i].id,
				answer : this.questions[i].getMyAnswer()
			});
		}
		var thisObj = this;
		$.ajax({
			url : thisObj.config.AJAXPATH + "?controller=quiz_random&action=getAnswers",
			data : {
				answersData : thisObj.answersData,
				id_level_subject : thisObj.id_level_subject
			},
			type : "POST",
			success : function(msg) {
				$.unblockUI();
				var obj = thisObj.answersData = jQuery.parseJSON(msg);
				for (var i = 0; i < obj.length; i++) {
					thisObj.questions[i].answerData = obj[i];
				}
				eval(nextFunction);
				thisObj.showResult();
			}
		});
	},
	showResult : function() {
		var str = "<table width='90%'>" + "<tr>" + "<td>" + il8n.count_right
				+ "</td>" + "<td>" + this.count.right + "</td>" + "</tr>"
				+ "<tr>" + "<td>"
				+ il8n.count_giveup + "</td>" + "<td>" + this.count.giveup
				+ "</td>" + "</tr>" + "<tr>" + "<td>" + il8n.count_questions
				+ "</td>" + "<td>" + this.count.total + "</td>" + "</tr>"
				+ "</table>";
		var ac = Ext.getCmp('ext_Operations');
		ac.layout.activeItem.collapse(false);
		ac.add({
					id : 'ext_randomResult',
					title : il8n.Quiz_randoms_Result,
					html : '<div id="randomresult">aaa</div>'
				});
		ac.doLayout();

		$("#randomresult").empty();
		$("#randomresult").append(str);

		$.blockUI({
					message : str
				});
		$('.blockOverlay').attr('title', 'Click to unblock').click($.unblockUI);
	}
});
