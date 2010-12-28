var wls_user = function(){
 
	/**
	 * 得到我个人的历次测验的成绩情况统计表
	 * */
	this.getMyQuizChart = function(domid,rewrite){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_record&action=getChartByPChart&rewrite="+rewrite,
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				$("#"+domid).attr("src",obj.path);
			}
		});
	}	
}
wls_user.prototype = new wls();
var user = new wls_user();