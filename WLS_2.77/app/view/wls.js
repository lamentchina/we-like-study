//全局变量,国际化语言设置
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
	
	/**
	 * 配置参数,全部是定义服务端的路径
	 * 比如AJAX地址,图片地址,各个第三方包文件的地址
	 * 
	 * 由于每个HTML页面在运行时都是独立的一个IFRAME,与母页面无关
	 * 因此基本上每个HTML页面上都会重新改写配置信息
	 * */
	this.config = {
		AJAXPATH : 'wls.php',
		libPath : '../libs/',
		filePath : '../file/',
		logOut : ''
	}

	/**
	 * 获取国际化语言包设置
	 * il8n 是一个全局变量(只能在本HTML页面生存的全局变量)
	 * 主要是向服务端读取所有的语言包数据,然后解析给前台赋值
	 * */
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
			}
		});
	}
	
	/**
	 * 得到URL里面的参数
	 * 服务端可以通过HTTP GET相关的操作来获得URL里面的各个参数
	 * 不过纯粹的HTML+JS也可以获得URL里面的参数
	 * */
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