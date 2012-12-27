var question_big = function(){
	this.initDom = function() {
		$("#wls_quiz_main").append("<div id='w_qs_" + this.id
				+ "'></div>");
		$("#w_qs_" + this.id).append("<div class='w_qw_title'>&nbsp;<span class='w_qw_tool'></span>"
				+ this.title + "</div>");
	}
	this.getMyAnswer = function(){return 'I_DONT_KNOW';	}
	
	this.showDescription = function(){}
	
	this.setMyAnswer = function(){}
};
question_big.prototype = new question();