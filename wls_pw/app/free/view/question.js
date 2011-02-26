wls.question = Ext.extend(wls, {

	id : null,
	index : null,
	id_quiz_paper : null,
	answerData : null,
	questionData : null,
	cent : null,
	mycent : null,
	type : null,
	parent : null,
	quiz : null

	,
	addWhyImWrong : function() {
		$('#w_qs_' + this.id)
				.append("<div class='WhyImWrong'>"
						+ "<table><tr>"
						+ "<td width='20%' style='color:red;font-weight:18px;'>"
						+ il8n.whyImWrong
						+ ":&nbsp;&nbsp;</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='1' name='"
						+ this.id
						+ "' />"
						+ il8n.whyImWrong_NotGoodEnough
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='2' name='"
						+ this.id
						+ "' />"
						+ il8n.whyImWrong_Carelese
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='3' name='"
						+ this.id
						+ "' />"
						+ il8n.whyImWrong_IHaveNoIdea
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='4' name='"
						+ this.id + "' />" + il8n.whyImWrong_WrongAnswer
						+ "</td>" + "</tr></table>" + "</div>");
	},
	addCommenter : function() {
		$('.w_q_d', $('#w_qs_' + this.id))
				.append("<div style='height:40px;'><input class='rating' value='1' /></div>");
		$('.rating', $('.w_q_d', $('#w_qs_' + this.id))).rating({
					callback : function(value, link) {

					}
				});
	},
	addListening : function() {

	}

});
var wls_question_saveComment = function(dom) {
	$(".WhyImWrong", $("#w_qs_" + dom.name)).empty();

	var obj = new wls();
	$.ajax({
		url : obj.config.AJAXPATH + "?controller=question&action=saveComment",
		data : {
			id : dom.name,
			value : dom.value
		},
		type : "POST",
		success : function(msg) {
			msg = jQuery.parseJSON(msg);

			var c1 = parseInt(msg.comment_ywrong_1);
			var c2 = parseInt(msg.comment_ywrong_2);
			var c3 = parseInt(msg.comment_ywrong_3);
			var c4 = parseInt(msg.comment_ywrong_4);

			var cc = [];
			cc.push(Math.floor((c1 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c2 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c3 * 100) / (c1 + c2 + c3 + c4)));
			cc.push(Math.floor((c4 * 100) / (c1 + c2 + c3 + c4)));
			var str = il8n.statistic + ":";
			for (var i = 0; i < 4; i++) {
				if (i == 0) {
					str += "<span style='background-color:red;color:red' title='"
							+ il8n.whyImWrong_NotGoodEnough + "," + c1 + "'>";
				} else if (i == 1) {
					str += "<span style='background-color:blue;color:blue' title='"
							+ il8n.whyImWrong_Carelese + "," + c2 + "'>";
				} else if (i == 2) {
					str += "<span style='background-color:gray;color:gray' title='"
							+ il8n.whyImWrong_IHaveNoIdea + "," + c3 + "'>";
				} else if (i == 3) {
					str += "<span style='background-color:yellow;color:yellow' title='"
							+ il8n.wyImWrong_WrongAnswer + "," + c4 + "'>";
				}
				for (ii = 0; ii < cc[i]; ii++) {
					str += "|";
				}
				str += "</span>";
			}
			$(".WhyImWrong", $("#w_qs_" + dom.name)).append(str);

		}
	});
}
var wls_question_toogle = function(id) {
	$('#w_qs_d_t_' + id, $('#w_qs_' + id)).toggleClass('w_q_d_t_2');
	$(".w_q_d", $('#w_qs_' + id)).toggleClass('w_q_d_h');
}