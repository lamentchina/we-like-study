/**
 * WLS,We-Like-Study,在线考试学习系统
 * 组合题题型,
 * 可能包含有听力题,使用FALSH播放器实现
 * */
wls.question.mixed = Ext.extend(wls.question, {
	initDom : function() {
		$("#wls_quiz_main").append("<div id='w_qs_" + this.id
				+ "'></div>");
		$("#w_qs_" + this.id).append("<div class='w_qw_title'>"
				+ this.index + "&nbsp;<span class='w_qw_tool'></span>"
				+ this.questionData.title + "</div>");
		if(parseInt(this.questionData.path_listen)!=0){
			var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="150" height="20" '
				   + 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab"> '
				   + '<param name="movie" value="'+this.quiz.config.libPath+'singleplayer/singlemp3player.swf?file='+this.questionData.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" /> '
				   + '<param name="wmode" value="transparent" /> '
				   + '<embed wmode="transparent" width="150" height="20" src="'+this.quiz.config.libPath+'singleplayer/singlemp3player.swf?file='+this.questionData.path_listen+'&autoStart=false&backColor=000000&frontColor=ffffff&songVolume=90" '
				   + 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /> '
				   + '</object> ';
			$(".w_qw_tool", "#w_qs_" + this.id).append(str);	
		}
		this.cent = this.questionData.cent;
		this.questionData = null;
	},
	showDescription : function() {

	},
	getMyAnswer : function() {
		return '';
	},
	setMyAnswer : function() {

	}
});