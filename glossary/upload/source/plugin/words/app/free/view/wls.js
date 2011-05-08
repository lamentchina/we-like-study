var il8n = null;

/**
 * WLS,We Like Study,一种在线考试学习系统
 * 由于前台引用了EXTJS,
 * 所以整个前台代码都被迫开源
 * (但这并不意味着后台代码也会开源)
 * 
 * @see www.welikestudy.com
 * @author wei1224hf, from China , mainland
 * */
var wls = function() {
	
	this.config = {
		AJAXPATH : 'wls.php',
		libPath : '../libs/',
		filePath : '../file/'
	}

	this.getIl8n = function(nextFunction){
		var thisObj = this;
		Ext.Ajax.request({
			method : 'POST',
			url : thisObj.config.AJAXPATH + "?controller=system&action=translateIniToJsClass",
			success : function(response) {
				var obj = Ext.decode(response.responseText);
				il8n = obj.il8n;

				eval(nextFunction);
			},
			failure : function(response) {
				alert('Net connection failed!');
			},
		});
	}
	
	this.getUrlPram = function(pram){
		var URLParams = new Array();
		var aParams = document.location.search.substr(1).split('&');
		for (i=0; i < aParams.length ; i++){
		   var aParam = aParams[i].split('=');
		   URLParams[aParam[0]] = aParam[1];
		}
		return URLParams[pram];
	}	
	
	this.author = 'wei1224hf';
	this.version = '2.77';
	this.see = 'www.welikestudy.com';
};