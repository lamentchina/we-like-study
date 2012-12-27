/**
 * 填空题现在不支持图片
 * 一道题里面,一般会有很多个填空输入框.
 * 填空题可以分为两个部分： 题干, 输入框
 * 因此填空题的题目都有 所属 关系,其 id_parent 字段很重要
 * 如果id_parent为0,说明这是一个题干,如果不为0,就说明是一个 输入框 
 *
 * @author wei1224hf@gmail.com QQgroup 135426431
 */
var question_blank = function() {
	//默认的 id_parent 为0,表示是题干,都是文字描述
	this.id_parent = 0;
	
	//在试卷页面上添加一个填空题
	this.initDom = function() {
		//如果这是题干部分,题干部分不会增加索引
		if(this.cent==0){
			$("#wls_quiz_main").append("<div id='w_qs_" + this.id + "'></div>");
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>&nbsp;<span class='w_qw_tool'></span><span class='w_qw_content'>"+this.title+"</span></div>");
			//如果这个填空题涉及听力,比如 英语考试 中的听力填空题 ,这就要引用一个FLASH播放器
			if(parseInt(this.path_listen)!=0){
				var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="150" height="20" '
					   + 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab"> '
					   + '<param name="movie" value="../sound/singlemp3player.swf?file=../sound/'+this.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" /> '
					   + '<param name="wmode" value="transparent" /> '
					   + '<embed wmode="transparent" width="150" height="20" src="../sound/singlemp3player.swf?file=../sound/'+this.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" '
					   + 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /> '
					   + '</object> ';
				$(".w_qw_tool", "#w_qs_" + this.id).append(str);
			}
		}else{
			//如果这是输入框部分
			$("[class=w_qw_tool]",$("#w_qs_"+this.id_parent)).append(this.index+"&nbsp;");
			var content = $(".w_qw_content",$("#w_qs_"+this.id_parent)).html();
			content = content.replace("["+this.title+"]","<input class='w_blank' index='"+this.title+"' id='w_qs_"+this.id+"' name='w_qs_"+this.id+"' onchange='question_done("+this.id+")' />");
			$(".w_qw_content",$("#w_qs_"+this.id_parent)).html(content);
		}
	};
	
	//填空题一般都是需要 人工批改 的,因为填空题的输入项可能各种各样,也就是说填空题的 答案 是形同虚设的,后台一般不会拿 答案去自动批改
	this.marking = function(){
		var c = $("#w_qs_"+this.id);
		c.attr('disabled',true);
		if(this.markingmethod==1){
			$('#w_q_subQuesNav_' + this.id).attr('class','w_q_sn_mark');
			$(c.next()).append("<a href='#' onclick='question_marking("+this.id_question_log+","+this.cent+","+this.id+")'>"+il8n.quiz.marking+"</a>");
		}
	};
	
	//显示解题说明
	this.showDescription = function() {
		if(this.id_parent==0)return;
		
		if(this.mode==3||this.mode==4){
			this.quiz.cent += parseFloat(this.cent);
			this.quiz.count.total++;
		}
		$('.w_qw_title',$("#w_qs_"+this.id_parent)).append("<div id='w_q_b_desc_"+this.id+"'>"+this.index+":"+this.description+"</div>");
		 
		 //如果这道题是需要人工批改的
		if(this.markingmethod==1){
			//需要人工批改的题目,必定是需要服务端处理的,必定是试卷模式 就要在试卷左侧的题目导航处处理一下高亮
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_mark');
			$('#w_q_subQuesNav_' + this.id).attr('title','人工批改,分值:' + (this.cent));
			//如果这是试卷模式中的 查看做题记录 ,就要在这道题的末尾额外标记 人工批改 
			if(this.quiz.type=='log'){
				var c = $("#w_qs_"+this.id);
				$(c.next()).append("<a href='#' onclick='question_markked("+this.quiz.logData.id+","+this.id+")'>人工批改</a>");
			}
		}else{
			//如果是需要系统自动批改的,就有比较复杂的判断逻辑了
			if (this.myAnswer == 'I_DONT_KNOW') {				
				$('#w_qs_' + this.id).attr("value",this.answer);
				$('#w_qs_' + this.id).attr("class","w_blank_dontknow");
				
				if(this.mode==3||this.mode==4){
					$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
					$('#w_q_subQuesNav_' + this.id).attr('title' , '放弃,分值:' + (this.cent));
					this.quiz.count.giveup++;
				}
			} else if (this.answer == this.myAnswer) {
				
				if(this.mode==3||this.mode==4){
					this.quiz.count.right++;
					this.quiz.mycent += parseFloat(obj.cent);
					$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_r');
					$('#w_q_subQuesNav_' + this.id).attr('title' , '作对,分值:' + (this.cent));	
				}
			} else {
				$('#w_qs_' + this.id).attr("class","w_blank_wrong");
				if(this.mode==3||this.mode==4){
					this.quiz.count.wrong++;
					$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_w');
					$('#w_q_subQuesNav_' + this.id).attr('title','做错,分值:' + (this.cent));
					if (this.quiz.type == 'paper'){
						this.addWhyImWrong();
					}
				}
			}
		}
	};

	this.getMyAnswer = function() {
		if(this.id_parent==0)return 'I_DONT_KNOW';
		var answer = '';
		var value = $('input[name=w_qs_' + this.id + ']').val();
		if (typeof(value) == 'undefined' || value=='') {
			answer = 'I_DONT_KNOW';
			if(this.mode==3||this.mode==4)this.quiz.count.giveup++;
		} else {
			answer = value;
		}
		return answer;
	};

	//在 查看做题记录 这个功能中,要使用这个函数
	this.setMyAnswer = function() {
		var myAnswer = this.myAnswer;
		if (myAnswer != 'I_DONT_KNOW') {
			var c = $("input[name=w_qs_" + this.id + "]");
			c.attr("value",myAnswer);
		}
	}
};
question_blank.prototype = new question();