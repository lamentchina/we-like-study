/**
 * 卷子
 * 这个类是 考卷,随机组卷 的父类
 * */
wls.quiz = Ext.extend(wls, {
	
	objName:'',
	questions : [],
	count : {
		giveup : 0,
		right : 0,
		wrong : 0,
		mannual : 0,
		total : 0
	},

	state : 0,
	cent : 0,        //卷子总分
	cent_ : 0,       //我的得分

	addQuestions : function() {
		var index = 1;
		for (var i = 0; i < this.questions.length; i++) {
			this.questions[i].quiz = this;
			this.questions[i].initDom();		
		}
		this.addNavigation();
		//this.status = 2;
	},
	
	/**
	 * 题目导航
	 */
	addNavigation : function() {
		var str = '';
		var index = 0;
		for (var i = 0; i < this.questions.length; i++) {
				str += "<div class='w_q_sn_undone' id='w_q_subQuesNav_"
						+ this.questions[i].id
						+ "' onclick='"
						+ this.objName
						+ ".wls_quiz_nav("
						+ this.questions[i].id
						+ ")' style='height:18px;'><a href='#' style='border:0px;font-size:10px;margin-top:2px;' >"
						+ (i+1) + "</a></div>";

		}
		$("#navigation").append(str);
	},
	
	/**
	 * 点击了题目导航处的序号
	 * 试卷会聚焦到这个题目处,并闪烁一下
	 * */
	wls_quiz_nav : function(id) {
		$("#wls_quiz_main").scrollTop($("#wls_quiz_main").scrollTop() * (-1));
		var num = $("#w_qs_" + id).offset().top - 150;
		$("#wls_quiz_main").scrollTop(num);
		$("#w_qs_" + id).fadeTo("slow", 0.33,function(){
			$("#w_qs_" + id).fadeTo("slow", 1);
		});		
	},
	
	/**
	 * 提交卷子
	 * */
	submit : function(){
	    for(var i=0;i<this.questions.length;i++){
	    	this.questions[i].submit();
	    }
	},
	
	/**
	 * 初始化页面布局
	 * */
	initLayout : function() {
		var thisObj = this;
		var viewport = new Ext.Viewport({
			layout : 'border',
			items : [{
						collapsible : false,
						region : 'center',
						margins : '5 0 0 0',
						html : '<div id="wls_quiz_main" class="w_q_container"></div>'
					}, {
						title : il8n.normal.operation,
						collapsible : true,
						layout : 'border',
						region : 'west',
						split : true,
						width : 200,
						minSize : 175,
						maxSize : 400,
						defaults : {
							collapsible : true,
							split : true,
							animFloat : false,
							autoHide : false,
							useSplitTips : true
						},
						items : [new Ext.Button({
									id : 'quiz_submit',
									text : il8n.normal.submit,
									region : 'south',
									height:40,
									handler : function() {
									    thisObj.submit();
									}
								}), {
							id : 'ext_Operations',
							collapsible : false,
							region : 'center',
							floatable : false,
							margins : '0 0 0 0',
							cmargins : '5 5 0 0',
							split : true,
							width : 200,
							minSize : 175,
							maxSize : 400,

							layout : {
								type : 'accordion',
								animate : true
							},
							items : [{
										id : 'ext_Navigation',
										title : il8n.normal.navigation,
										html : '<div id="navigation"></div>'
									}, {
										id : 'ext_Brief',
										title : il8n.normal.title,
										html : '<div id="paperBrief"></div>'
									}]

						}]
					}]
		});
		$("#wls_quiz_main").css("height", $(document).height() - 10);
	}
});
