/**
 * 题目来源于一张卷子(paper),
 * */
wls.question = Ext.extend(wls, {

    id : null,               //索引编号,在一张卷子里的题目序号,比如 1 2 3 4 5
	id_db : null,            //在数据库表 题目表(wls_questions) 中的编号
	id_parent : null,        //在'阅读理解','完型填空','短文听力'等大题中使用,让子题目指向母题目
	id_quiz_paper : null,    //这个题目属于哪张试卷.WLS系统中,每个题目都是属于某一张特定的试卷的,其值应该是 wls_quiz_paper表中的id
	id_question_log : null,  //这个题目的做题日志 wls_question_log 表中的id
	
	answerData : null,       //这是一个Object,内含 正确答案,用户提交的答案,解题思路
	questionData : null,     //这是一个Object,内含各个题目选项.如果体型是 填空题 或 简答题,则只包含题目描述(title)
	
	cent : null,             //这个题目的分值
	cent_ : null,            //用户答题后所获得的分值,一般而言,要么是0,要么就等于cent.但简答题则不同
	
	type : null,             //题型,字符串,可以是 单选题,多选题,判断题,填空题等
	quiz : null,             //试卷对象.	
	
	markingmethod : 0,       //卷子批改方式:自动批改 或 教师人工批改
	
	/** 
	 * 用户提交试卷之后,发现这一题做错了,如果仍然觉得自己没有做错,认为是题目本身错了,可以对这个题目进行评论,
	 * 评论之后,系统会立刻将评论提交给系统后台插入到数据库,并统计所有用户对这道题的综合评价,
	 * 后台再将这个评价结论返回给前台,前台再分色显示统计结果
	 * */
	whyImWrong : function() {
		$('#w_qs_' + this.id)
				.append("<div class='WhyImWrong'>"
						+ "<table><tr style='color:red;font-size:11px;'>"
						+ "<td width='20%' >"
						+ il8n.quiz.whyImWrong
						+ ":&nbsp;&nbsp;</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='1' name='"
						+ this.id
						+ "' />"
						+ il8n.quiz.whyImWrong_NotGoodEnough
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='2' name='"
						+ this.id
						+ "' />"
						+ il8n.quiz.whyImWrong_Carelese
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='3' name='"
						+ this.id
						+ "' />"
						+ il8n.quiz.whyImWrong_IHaveNoIdea
						+ "</td>"
						+ "<td width='20%'><input type='radio' onchange='wls_question_saveComment(this)' value='4' name='"
						+ this.id + "' />" + il8n.quiz.whyImWrong_WrongAnswer
						+ "</td>" + "</tr></table>" + "</div>");
	}

});

/**
 * */
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
 * 如果这道题是 简答题 或 填空题,那么这道题就可能需要 人工批改.
 * 教师在登录系统之后,会收到这个批改任务,批改之后评分并写下评分依据,
 * 提交之后数据就会入到后台数据库
 * */
var wls_question_marking = function(id_question,cent,id_index){
	var win = new Ext.Window({
		id : id_question,
		title : il8n.quiz.marking,
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
		 						fieldLabel : il8n.quiz.marking,
		 						width : 150,
		 						xtype: 'radiogroup',

		 			            items: [
		 			                   {boxLabel: il8n.quiz.right, name: 'correct', inputValue: 1, checked: true},
		 			                   {boxLabel: il8n.quiz.wrong, name: 'correct', inputValue: 0}
		 			               ]

		 					}, {
		 						fieldLabel : il8n.quiz.myScore,
		 						width : 150,
		 						xtype: 'spinnerfield',
		 			           	minValue: 0,
		 		            	maxValue: cent,
		 		            	value:cent,
		 						name : 'mycent',
		 						allowBlank : false
		 					}, {
		 						fieldLabel : il8n.quiz.comment,
		 						width : 150,
		 						name : 'comment',
		 						allowBlank : false
		 					}],

		 			buttons : [{
	 						text : il8n.quiz.marking,
	 						handler : function() {
				 				var form = Ext.getCmp('wls_q_marking_w').getForm();
		
				 				if (form.isValid()) {
				 					$.blockUI({
				 								message : '<h1>' + il8n.normal.loading + '......</h1>'
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
				 					Ext.Msg.alert(il8n.normal.fail, il8n.RequesttedImputMissing);
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
		 						fieldLabel : il8n.quiz.marking,
		 						width : 150,
		 						name: 'correct'

		 					}, {
		 						fieldLabel : il8n.quiz.myScore,
		 						width : 150,
		 						name : 'mycent'

		 					}, {
		 						fieldLabel : il8n.quiz.comment,
		 						width : 150,
		 						name : 'comment'
		 					}, {
		 						fieldLabel : il8n.normal.status,
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