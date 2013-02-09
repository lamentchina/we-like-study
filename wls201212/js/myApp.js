var il8n = {};
var myAppServer = function(){
	return "../php/myApp.php?random="+Math.random();
};
var initWebpage = function(afterAjax){
	// 页面是单独打开的,可能是处于测试或开发需要,
	if (top === self) { 
		var noLogin = getParameter("noLogin", window.location.toString() );
		// 服务端不需要登录,直接从后台读取所有权限数据和所有语言包
		if(noLogin=="1"){
			$.ajax({
				url : myAppServer()+"&class=basic_developer&function=giveMeAll",
				type : "GET",
				dataType: 'json',
				success : function(response) {
					top.il8n = response.il8n;
					top.basic_user.permission = response.permission;
					if ( typeof(afterAjax) == "string" ){
						eval(afterAjax);
					}else if( typeof(afterAjax) == "function"){
						afterAjax();
					}
				}
			});
		}else{
			var username = getParameter("username", window.location.toString() );
			var password = MD5( MD5( getParameter("password", window.location.toString()) ) +((new Date()).getHours())) ;
			basic_user.login(username,password,afterAjax);
		}
	} else {
		eval(afterAjax);
	}
}

