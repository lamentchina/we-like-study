wls.question = Ext.extend(wls, {

	id : null,
	index : null,
	id_quiz_paper : null,
	answerData : null,
	questionData : null,
	id_question_log : null,
	cent : null,
	mycent : null,
	type : null,
	parent : null,
	quiz : null,
	markingmethod : 0,
	addWhyImWrong : function() {
		$('#w_qs_' + this.id)
				.append("<div class='WhyImWrong'>"
						+ "<table><tr style='color:red;font-size:11px;'>"
						+ "<td width='20%' >"
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
							+ il8n.whyImWrong_WrongAnswer + "," + c4 + "'>";
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
var wls_question_done = function(id){
	if($('#w_q_subQuesNav_' + id).hasClass('w_q_sn_undone')){
		$('#w_q_subQuesNav_' + id).attr('class','w_q_sn_done');
	}
}
/**
 * The teacher can mark paper,exam,homework and so on
 * The question's markingMethod must setted to be 'marking by teacher' , the flag is 1. 
 * By default the flag is 0 , which means 'marking automaticly'
 * 
 * @param id_question Databse table wls_question's id
 * @param cent The question's score. 
 * @param id_index See database table wls_quiz::ids_questions . The position of id_question in ids_questions
 * */
var wls_question_marking = function(id_question,cent,id_index){
	var win = new Ext.Window({
		id : id_question,
		title : il8n.marking,
		width : 300,
		height : 200,
		modal : true,
		layout : 'fit',
		items : [
		         new Ext.form.FormPanel({
		 			id : 'wls_q_marking_w',
		 			labelWidth : 75,
		 			frame : true,
		 			bodyStyle : 'padding:5px 5px 0',
		 			width : 350,
		 			defaults : {
		 				width : 100
		 			},
		 			defaultType : 'textfield',

		 			items : [{
		 						fieldLabel : il8n.marking,
		 						width : 150,
		 						xtype: 'radiogroup',

		 			            items: [
		 			                   {boxLabel: il8n.right, name: 'correct', inputValue: 1, checked: true},
		 			                   {boxLabel: il8n.wrong, name: 'correct', inputValue: 0}
		 			               ]

		 					}, {
		 						fieldLabel : il8n.myScore,
		 						width : 150,
		 						xtype: 'spinnerfield',
		 			           	minValue: 0,
		 		            	maxValue: cent,
		 		            	value:cent,
		 						name : 'mycent',
		 						allowBlank : false
		 					}, {
		 						fieldLabel : il8n.comment,
		 						width : 150,
		 						name : 'comment',
		 						allowBlank : false
		 					}],

		 			buttons : [{
	 						text : il8n.marking,
	 						handler : function() {
				 				var form = Ext.getCmp('wls_q_marking_w').getForm();
		
				 				if (form.isValid()) {
				 					$.blockUI({
				 								message : '<h1>' + il8n.loading + '......</h1>'
				 							});
				 					var obj = form.getValues();
				 					obj.id = id_question;
				 					obj.markked = 1;
				 					Ext.Ajax.request({
				 						method : 'POST',
				 						url : "wls.php?controller=user_teacher&action=marking&temp=" + Math.random(),
				 						success : function(response) {
				 							$.unblockUI();
				 							
				 							if($('#w_q_subQuesNav_' + id_index).hasClass('w_q_sn_mark')){
				 								$('#w_q_subQuesNav_' + id_index).attr('class','w_q_sn_markked');
				 							}
				 							
				 							//var obj = jQuery.parseJSON(response.responseText);
				 						},
				 						failure : function(response) {
				 							
				 						},
				 						params : obj
				 					});
				 				} else {
				 					Ext.Msg.alert(il8n.fail, il8n.RequesttedImputMissing);
				 				}
	 						}
	 					}
		 			]
		 		})
		         ]
		
	});
	win.show();
}
var wls_question_markked = function(id_quiz_log,index){

	var win2 = new Ext.Window({
		id : 'markked_' + id_quiz_log,
		title : il8n.marking,
		width : 300,
		height : 200,
		modal : true,
		layout : 'fit',
		items : [
		         new Ext.form.FormPanel({
		 			id : 'wls_q_markked_w',
		 			labelWidth : 75,
		 			frame : true,
		 			bodyStyle : 'padding:5px 5px 0',
		 			width : 350,
		 			defaults : {
		 				width : 100
		 			},
		 			defaultType : 'textfield',

		 			items : [{
		 						fieldLabel : il8n.marking,
		 						width : 150,
		 						name: 'correct'

		 					}, {
		 						fieldLabel : il8n.myScore,
		 						width : 150,
		 						name : 'mycent'

		 					}, {
		 						fieldLabel : il8n.comment,
		 						width : 150,
		 						name : 'comment'
		 					}, {
		 						fieldLabel : il8n.state,
		 						width : 150,
		 						name : 'markked'
		 					}]
		         })
		       ]
	
		
	});
	win2.show();
	
	var data =  {id_quiz_log:id_quiz_log,index:index};

	Ext.Ajax.request({
		method : 'POST',
		url : "wls.php?controller=question_log&action=getMarkInfo&temp=" + Math.random(),
		success : function(response) {				
			var obj = jQuery.parseJSON(response.responseText);
			var form = Ext.getCmp('wls_q_markked_w').getForm();
			form.setValues(obj);	
		},
		failure : function(response) {
			
		},
		params : data
	});
	
}