var wls_user = function(){
 this.getChart = function(domid){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz_record&action=getList&returnType=json",
			success: function(msg){
				var obj = jQuery.parseJSON(msg);
				var data = [];
				for(var i=0;i<obj.rows.length;i++){
					data.push([i,parseInt(obj.rows[i].proportion*100)]);
				}
			    $.plot($("#"+domid), [data]);
			}
		});
	}	
}
wls_user.prototype = new wls();
var user = new wls_user();