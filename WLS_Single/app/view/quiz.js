/**
 * 卷子
 * 这个类是 考卷,随机组卷 的父类
 * */
wls.quiz = Ext.extend(wls, {
	
	objName:'',
	questions : [],   //题目集
	count : {
		giveup : 0,   //漏题数量,放弃不做的
		right : 0,    //作对数
		wrong : 0,    //做错
		total : 0     //题目总数
	},

	state : '',       //试卷状态 
	cent : 0,         //卷子总分
	cent_ : 0,        //我的得分

	/**
	 * 在页面上初始化所有题目
	 * 每一种题型,都有 initDom() 这个函数
	 * */
	initQuestions : function() {
		var index = 1;
		for (var i = 0; i < this.questions.length; i++) {
			this.questions[i].quiz = this;
			this.questions[i].initDom();		
		}
		this.addNavigation();
	},
	
	/**
	 * 在题目导航处,依次添加各个题目的导航按钮
	 * 点击导航按钮,右侧的卷子就会翻滚定位
	 * */
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
	 * 试卷会翻滚定位到这个题目处,并闪烁一下
	 * */
	wls_quiz_nav : function(id) {
		$("#wls_quiz_main").scrollTop($("#wls_quiz_main").scrollTop() * (-1));
		var num = $("#w_qs_" + id).offset().top - 150;
		var target = $('#ext_wls_quiz_main>div>div>div').find('#w_qs_'+id);
		$('#ext_wls_quiz_main>div>div').scrollTo(target, 800);
			
	},
	
	/**
	 * 提交卷子
	 * 统计做题结果:做错数,做对数,放弃数,还有分数
	 * TODO 知识点统计
	 * */
	submit : function(){
		if(this.state=='submitted')return;
		Ext.getCmp('ext_Operations').getLayout().setActiveItem('ext_Brief');
	    for(var i=0;i<this.questions.length;i++){
	    	var result = this.questions[i].submit();
	    	if(result=='RIGHT'){
	    		this.count.right ++;
	    	}else if(result=='WRONG'){
	    		this.count.wrong ++;
	    	}else if(result=='I_DONT_KNOW'){
	    		this.count.giveup ++;
	    	}
	    	this.cent += parseFloat(this.questions[i].cent);
	    	this.cent_ += parseFloat(this.questions[i].cent_);
	    }
	    $('#paperBrief').append( il8n.quiz.myCent+':<span style="color:red;font-size:25px;">'+this.cent_ + '</span><br/><br/>' + il8n.quiz.totalCent+':'+this.cent  );
	    $('#paperBrief').append('<br/>' + il8n.quiz.right+':'+this.count.right + ' <br/> ' + il8n.quiz.wrong+':'+this.count.wrong + ' <br/> ' + il8n.quiz.giveup+':'+this.count.giveup );
		$('#paperBrief').fadeTo("slow", 0.2,function(){
		    $("#paperBrief").fadeTo("slow", 1);
		});	
		this.state = 'submitted';
	},

	/**
	 * 初始化页面布局
	 * 页面分为左右两部分,
	 *   左边部分是导航按钮 , 做题统计 , 题目提交按钮 ,被包含在一个 Accordion Panel 空间中
	 *   右边部分是卷子的题目浏览
	 * */
	initLayout : function() {
		var thisObj = this;
		var viewport = new Ext.Panel({
			layout : 'border',
			items : [{
                        id:'ext_wls_quiz_main',
						collapsible : false,
						region : 'center',
						margins : '5 0 0 0',
						autoScroll : true,
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
								type : 'accordion'
							},
							items : [{
										id : 'ext_Navigation',
										title : il8n.normal.navigation,
										html : '<div id="navigation"></div>'
									}, {
										id : 'ext_Brief',
										title : il8n.normal.title,
										html : '<div id="paperBrief" style="background-color:#E7EFF0"></div>'
									}]

						}]
					}]
		});
		$("#wls_quiz_main").css("height", $(document).height() - 10);
		
		return viewport;
	},
	
	store : function(data){}      //接口函数,导入外部数据集,解析为题目
});
