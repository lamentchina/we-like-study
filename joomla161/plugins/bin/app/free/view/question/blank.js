wls.question.blank = Ext.extend(wls.question, {
	id_parent : 1
	,initDom : function() {
		if(this.questionData.id_parent==0){
			this.id_parent = 0;
			$("#wls_quiz_main").append("<div id='w_qs_" + this.id
					+ "'></div>");
			$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
					+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
					+ this.questionData.title + "</div>");

			if(parseInt(this.questionData.path_listen)!=0){

				var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="150" height="20" '
					   + 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab"> '
					   + '<param name="movie" value="../libs/singleplayer/singlemp3player.swf?file='+this.questionData.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" /> '
					   + '<param name="wmode" value="transparent" /> '
					   + '<embed wmode="transparent" width="150" height="20" src="../libs/singleplayer/singlemp3player.swf?file='+this.questionData.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" '
					   + 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /> '
					   + '</object> ';
				$(".w_qw_tool", "#w_qs_" + this.id).append(str);	
			}
		}else{
			$("[class=w_qw_tool]",$("#w_qs_"+this.questionData.id_parent)).append(this.index+"&nbsp;");
			$("[index="+this.questionData.title+"]",$("#w_qs_"+this.questionData.id_parent)).attr("id","w_qs_"+this.id);
			$("[index="+this.questionData.title+"]",$("#w_qs_"+this.questionData.id_parent)).attr("name","w_qs_"+this.id);
			$("[index="+this.questionData.title+"]",$("#w_qs_"+this.questionData.id_parent)).attr("onchange","wls_question_done("+this.id+")");
		}
		this.cent = this.questionData.cent;
		this.questionData = null;
	},
	
	marking : function(){
		var c = $("#w_qs_"+this.id);
		c.attr('disabled',true);

		if(this.markingmethod==1){
			$('#w_q_subQuesNav_' + this.id).attr('class','w_q_sn_mark');
			$(c.next()).append("<a href='#' onclick='wls_question_marking("+this.id_question_log+","+this.cent+","+this.id+")'>"+il8n.marking+"</a>");
		}
	}
	,
	showDescription : function() {

		if(this.id_parent==0)return;
		var obj = this.answerData;
		this.quiz.cent += parseFloat(obj.cent);
		this.quiz.count.total++;		
		 $('#w_qs_' + this.id).attr("title",obj.answer+" "+obj.description);
		 
		if(obj.markingmethod==1){
			$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_mark');
			$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.markingByTeacher + ',' + il8n.cent + ':' + (this.cent));
			if(this.quiz.type=='log'){
				var c = $("#w_qs_"+this.id);
				$(c.next()).append("<a href='#' onclick='wls_question_markked("+this.quiz.logData.id+","+this.id+")'>"+il8n.marking+"</a>");
			}

		}else{		 
			if (obj.myAnswer == 'I_DONT_KNOW') {
				this.quiz.count.giveup++;
				 $('#w_qs_' + this.id).attr("value",obj.answer);
				$('#w_qs_' + this.id).attr("class","w_blank_dontknow");
				$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_g');
				$('#w_q_subQuesNav_' + this.id).attr('title',
						il8n.giveup + ',' + il8n.cent + ':' + (this.cent));
				wls_question_toogle(this.id);
			} else if (obj.answer == obj.myAnswer) {			
				this.quiz.count.right++;
				this.quiz.mycent += parseFloat(obj.cent);
				$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_r');
				$('#w_q_subQuesNav_' + this.id).attr('title',
					il8n.right + ',' + il8n.cent + ':' + (this.cent));
				wls_question_toogle(this.id);
			} else {
				this.quiz.count.wrong++;
				$('#w_qs_' + this.id).attr("class","w_blank_wrong");
				$('#w_q_subQuesNav_' + this.id).addClass('w_q_sn_w');
				$('#w_q_subQuesNav_' + this.id).attr('title',
						il8n.wrong + ',' + il8n.cent + ':' + (this.cent));
				if (this.quiz.type == 'paper')
					this.addWhyImWrong();
			}
		}

		obj = null;
	},
	
	getMyAnswer : function() {
		var answer = '';
		var value = $('input[name=w_qs_' + this.id + ']').val();
		if (typeof(value) == 'undefined' || value=='') {
			answer = 'I_DONT_KNOW';
			this.quiz.count.giveup++;
		} else {
			answer = value;
		}
		return answer;

	},
	
	setMyAnswer : function() {
		var myAnswer = this.answerData.myAnswer;
		if (myAnswer != 'I_DONT_KNOW') {
			var c = $("input[name=w_qs_" + this.id + "]");
			c.attr("value",myAnswer);
		}
	}

});