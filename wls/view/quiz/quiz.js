var toogleDesc = function(id){
	$('#w_qs_d_t_'+id,$('#w_qs_'+id)).toggleClass('w_q_d_t_2');
	$(".w_q_d",$('#w_qs_'+id)).toggleClass('w_q_d_h');
}

/**
 * 我们爱学习
 * We Like Study, wls
 * 测验卷类
 * quiz
 * */
var wls_quiz = function(){
	//全局变量的名称
	this.naming = '';
	
	//测验卷的配置内容
	this.questions = [];//主题目列表
	this.quesId = [];
	this.quesId_parsed = [];
	this.questions_sub = [];//子题目列表
	this.quesId_sub = [];//子题目编号
	this.type = 'paper';
	
	this.count_wrong = 0;
	this.count_total = 0;
	this.count_giveup = 0;
	this.count_manual  = 0;//需要人工批改的题目数
	this.count_right = 0;
	
	this.parseQuesWay = 'oneByOne';//all
	this.submitQuesWay = 'oneByOne';//onceAll
	
	//测验卷所在的DOM 编号
	this.quizDomId = '';
	
	//初始化测验卷的布局
	this.initLayout = function(){
		$('#'+this.quizDomId).addClass('w_q');
		$('#'+this.quizDomId).empty();
		$('#'+this.quizDomId).append("<div class='w_q_container'></div><div class='w_q_sidebar'><div class='subQuesNav'></div></div>");
		$(".w_q_container",$('#'+this.quizDomId)).css('height',$(document).height()-15);
		$(".w_q_sidebar",$('#'+this.quizDomId)).css('height',$(document).height()-15);
	}
	
	/**
	 * 如果题目的数据没有初始化的话
	 * 通过AJAX将每个题目的数据都初始化
	 * */
	this.quesListAJAXData = function(index,nextFunction){
		if(index==0){//读取题目数据开始
			 $.blockUI({
				 message: '<h1>正在读取数据...</h1>', 
				 css: { 
		            border: 'none', 
		            padding: '15px', 
		            backgroundColor: '#000', 
		            '-webkit-border-radius': '10px', 
		            '-moz-border-radius': '10px', 
		            opacity: .5, 
		            color: '#fff' 
		        } }); 
		}else if(index==this.questions.length){//读取题目数据结束
			this.parseQuesList(index-1);
			eval(nextFunction);
			if(this.type=='paper'){
				this.getClock();
			}
			this.addSubQuesNav();
			$.unblockUI();
			
			return;
		}else{
			this.parseQuesList(index-1,null);
		}
		var funstr = this.naming+".quesListAJAXData("+(index+1)+",'"+nextFunction+"');";
		this.questions[index].AJAXData(funstr);
	}
	
	this.parseQuesList = function(index,nextFunction){
		if(index==0){//题目初始化开始
			$('.w_q_container',$('#'+this.quizDomId)).empty();
			$('.w_q_sidebar',$('#'+this.quizDomId)).empty();
			if(this.type=='paper'){
				$('.w_q_sidebar').append('<div class="w_q_title">'+this.paperTitle+'</div>');
			}
		}
		if(index==this.questions.length){//题目初始化结束
			return;
		}		
		this.questions[index].initDom(nextFunction);
	}
	
	/**
	 * 在测验卷的右侧标注题目导航
	 * */
	this.addSubQuesNav = function(){
		$(".w_q_sidebar",$('#'+this.quizDomId)).append("<div class='w_q_subQuesNav'><div class='w_q_subQuesNav_title'>题目导航</div></div>");
		var str = '';
		$(".w_q_subQuesNav").css('height',((this.quesId_sub.length/6)*20+25)+'px');
		for(var i=0;i<this.quesId_sub.length;i++){
			var temp = i+1;
			if(temp<10){
				temp = '0'+temp;
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.quesId_sub[i]+"' onclick='"+this.naming+".scroll("+this.quesId_sub[i]+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else if(temp>=10 && temp <100){
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.quesId_sub[i]+"' onclick='"+this.naming+".scroll("+this.quesId_sub[i]+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else{
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.quesId_sub[i]+"' onclick='"+this.naming+".scroll("+this.quesId_sub[i]+")' style='height:18px;'><a href='#' style='border:0px;font-size:10px;margin-top:2px;' >"+temp+"</a></div>";
			}			
		}
		$(".w_q_subQuesNav",$(".w_q_sidebar",$('#'+this.quizDomId))).append(str);
	}
	
	/**
	 * 点击右侧的题目序号,左侧的试卷会滚动,
	 * 可以快速定位到相应的题目位置
	 * */
	this.scroll = function(id){
		$(".w_q_container").scrollTop($(".w_q_container").scrollTop()*(-1));
		var num = $("#w_qs__"+id).offset().top-150;
		$(".w_q_container").scrollTop(num);
	}
	
	/**
	 * 根据测验卷的主题目编号和主题目类型,
	 * 初始化各个题类,
	 * 类初始化结束之后,一般而言要初始化各个题目的详细数据
	 * */
	this.initQuestions = function(){
		for(var i=0;i<this.quesId.length;i++){
			var ques = null;
			switch(this.quesId[i].type){
				case '4':
					ques = new wls_question_blank();
					break;
				case '5':
					ques = new wls_question_reading();
					break;						
				case '1':
					ques = new wls_question_choice();
					break;					
				default:break;
			}
			if(ques!=null){
				ques.quesDomId = 'w_qs_'+this.quesId[i].id;
				ques.quesid = this.quesId[i].id;
				ques.targetDom = $('.w_q_container',$('#'+this.quizDomId));
				ques.quiz = this;
			}
			this.questions.push(ques);
		}

		this.quesListAJAXData(0,this.naming+'.initButton()');
	}	
	
	/**
	 * 涉及AJAX
	 * */
	this.submit = function(){
		$('.w_q_button',$('.w_q_sidebar')).css('display','none');
//		$(".w_q_sidebar").remove(".w_q_button");
		$.blockUI({
			 message: '<h1>正在提交...</h1>', 
			 css: { 
	            border: 'none', 
	            padding: '15px', 
	            backgroundColor: '#000', 
	            '-webkit-border-radius': '10px', 
	            '-moz-border-radius': '10px', 
	            opacity: .5, 
	            color: '#fff' 
	    } }); 
		this.questions_sub = [];
		for(var i=0;i<this.questions.length;i++){
			if(this.questions[i].type!=5){
				this.questions_sub.push(this.questions[i]);
			}else{
				for(var j=0;j<this.questions[i].childQuestions.length;j++){
					this.questions_sub.push(this.questions[i].childQuestions[j]);
				}
			}			
		}
		
		if(this.submitQuesWay=='onceAll' && this.type=='paper'){
			this.isMyMoneyEnough(this.naming+".submit3(null)");	
		}else{
			this.submit2(0);
		}		
	}
	 
	this.submit2 = function(index){
		if(index==this.questions_sub.length){
			if(this.type=='paper'){
				this.submitPaper();
			}
			
			this.questions_sub = null;
			this.quesId = null;
			this.quesId_parsed = null;
			this.quesId_sub = null;
			if(this.type=='paper'){
				this.paperData = null;
			}
			$.unblockUI();
			return;
		}
		var funstr = this.naming+'.submit2('+(index+1)+')';
		this.questions_sub[index].submit(funstr);
	}
	
	this.getMyAnswers = function(){
		var myAnswers = [];
		for(var i=0;i<this.questions_sub.length;i++){
			myAnswers.push({myAnswer:this.questions_sub[i].getMyAnswer(),id:this.questions_sub[i].quesid})
		}
		return myAnswers;
	}	
	
	this.submit3 = function(nextFunction){
		var thisObj = this;
		this.count_giveup = 0;
		var myAnswers = this.getMyAnswers();
		if(this.count_giveup==this.questions_sub.length){
			$.unblockUI();
			alert('放弃的题目太多,无效!');
			$('.w_q_button',$('.w_q_sidebar')).css('display','');
			return;
		}

		this.count_giveup = 0;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_quiz&action=checkAllOnce",
			data: {myAnswers:myAnswers},
			type: "POST",
			success: function(msg){
				$.unblockUI();
				var obj = jQuery.parseJSON(msg);
				for(var i=0;i<obj.length;i++){
					thisObj.questions_sub[i].answer = obj[i].answer;
					thisObj.questions_sub[i].description = obj[i].description;
					thisObj.questions_sub[i].markingmethod = obj[i].markingmethod;
					if(obj[i].correct==0){
						thisObj.questions_sub[i].wrong();
					}else if(obj[i].correct==2){
						thisObj.questions_sub[i].giveUp();
					}else if(obj[i].correct==3){
						thisObj.questions_sub[i].needManual();
					}else if(obj[i].correct==1){
						thisObj.questions_sub[i].right();
					}
				}
				if(thisObj.type=='paper'){
					thisObj.submitPaper();
				}
				thisObj.questions = null;
				eval(nextFunction);
			}
		});
	}
	
	//当所有题目都初始化结束之后,初始化测验卷的各个按钮
	this.initButton = function(){
		$('.w_q_sidebar',$('#'+this.quizDomId)).append("<button onclick='"+this.naming+".submit();' class='w_q_button'>提交试卷</button>");
	}
	
}
wls_quiz.prototype = new wls();