/**
 * web-desktop 的主界面
 * 
 * @version 201206
 * @author wei1224hf@gmail.com
 * */
var desktop = {
	LINKWIDTH : 90,
	LINKHEIGHT : 90, 
	TASKBARHEIGHT : 43,
	links : [],
	isWindow : false,

	/**
	 * 点击图标后的弹框
	 * TODO 弹框的大小,需要额外的代码控制,应该在配置文件中提供
	 * */
	f_open : function f_open(url, title, icon , type ,code) {
		if(top.$.ligerui.get("win_"+code)){
			top.$.ligerui.get("win_"+code).show();
			return;
		}
		var hei = 550;
		var wid = 650;
		if(type==1){
			hei = 200;
			wid = 450;
		}
		if(code=='10'){//登录
			hei = 220;
			wid = 340;
		}
		if(code=='17'){
			hei = 550;
			wid = 690;
		}		
		if(code=='14'){
			hei = 280;
			wid = 400;
		}
		if(code=='16'){//多人统一考试
			hei = 550;
			wid = 800;
		}			
		var win = top.$.ligerDialog.open({ 
			id : "win_"+code
			, isHidden:false 
			, height:  hei
			, url: url
			, width: wid
			, showMax: true
			, showToggle: true
			, showMin: true
			, isResize: true
			, modal: false
			, title: title
			, slide: false
		});
		win.close = function(){
			var g = this, p = this.options;
			top.$.ligerui.win.removeTask(this);
			g.unmask();
			g._removeDialog();
			top.$.ligerui.remove(top.$.ligerui.get("win_"+code));
			$('body').unbind('keydown.dialog');
		}
		
		return win;
	},
	
	/**
	 * 当屏幕被拉伸的时候,布局会改变
	 * */
	onResize : function() {		
		var linksHeight = $(window).height() - this.TASKBARHEIGHT;
		var winlinks = $("#winlinks");
		winlinks.height(linksHeight);
		var colMaxNumber = parseInt(linksHeight / this.LINKHEIGHT);//一列最多显示几个快捷方式
		for (var i = 0, l = desktop.links.length; i < l; i++) {
			
			var link = desktop.links[i];
			var jlink = $("li[linkindex=" + i + "]", winlinks);

			var top = (i % colMaxNumber) * this.LINKHEIGHT, left = parseInt(i / colMaxNumber) * this.LINKWIDTH;
			if (isNaN(top) || isNaN(left)) continue;
			var css = { top: top, left: left };

			jlink.css(css);
		}
	},
	
	/**
	 * 在主页面(桌面)上初始化各个功能的图标
	 * 先将可能已有的图标全删除掉,
	 * 再根据 links 这个数组中的内容,逐一添加一个DOM元素
	 */
	linksInit : function() {
		$("li",$('#winlinks')).remove();
		for (var i = 0, l = this.links.length; i < l; i++) {
			var link = this.links[i];
			var jlink;
			var jlink = $("<li></li>");
			jlink.attr("linkindex", i);
			jlink.append("<img src='" + link.icon + "' />");
			jlink.append("<span>" + link.title + "</span>");
			jlink.append("<div class='bg'></div>");
			jlink.hover(function () {
				$(this).addClass("l-over");
			}, function () {
				$(this).removeClass("l-over");
			}).click(function () {
				var linkindex = $(this).attr("linkindex");
				var link = desktop.links[linkindex];
				desktop.f_open(link.url, link.title, link.icon,link.type ,link.code);
			});
			jlink.appendTo($("#winlinks ul"));
		}
	},

	/**
	 * 在用户登录之后,通过用户的 权限编码 ,
	 * 来初始化用户桌面图标项
	 * */
	loadIcons : function(){
		desktop.links = [];

		var permission = basic_user.permission ;
		for(var i=0;i<permission.length;i++){					
			if(permission[i].type==1){
				desktop.links.push({icon:"../file/"+permission[i].icon,title: permission[i].name, url: 'win.html?id='+i,type:permission[i].type , code :permission[i].code });
			}else if(permission[i].type==2){
				desktop.links.push({icon:"../file/"+permission[i].icon,title: permission[i].name, url: permission[i].path,type:permission[i].type , code :permission[i].code });
			}
		}
		$(window).resize(desktop.onResize);
		$.ligerui.win.removeTaskbar = function () { }; //不允许移除
		$.ligerui.win.createTaskbar(); //页面加载时创建任务栏				
		desktop.linksInit();
		desktop.onResize();
		
		if(basic_user.username!='guest'){
			basic_workflow.check();
		}
	},
	
	initDom : function(){
		$(document.body).attr('style','overflow: hidden; ');
		$(document.body).append('<img width="100%" height="100%" src="../file/lansemeilibeijingshejibizhi_459706_m.jpg"/>');
		$(document.body).append('<div id="winlinks"><ul></ul></div>');
		var username = getCookie("myApp_username");
		var password = getCookie("myApp_password");
		if(username==null){
			basic_user.login("guest",MD5( MD5( "guest" ) +((new Date()).getHours())),"desktop.loadIcons();");
		}else{
			basic_user.login(username,password,"desktop.loadIcons();")
		}
	},
	
	initWinDesktop : function(){
		this.isWindow = true;
		var id = getParameter( "id" , window.location.toString() );
		$(document.body).attr('style','overflow: hidden; ');
		$(document.body).attr('style','overflow: hidden; background: url(../file/aaice_1.gif) ');
		$(document.body).append('<div id="winlinks"><ul></ul></div>');
		
		var permission = top.basic_user.permission;

		permission = permission[id].children;
		for(var i=0;i<permission.length;i++){
			if(permission[i].isleaf==1){
				desktop.links.push({icon:"../img/"+permission[i].icon,title: permission[i].name, url: 'win.html?id='+i ,  code :permission[i].code });
			}else{
				desktop.links.push({icon:"../img/"+permission[i].icon,title: permission[i].name, url: permission[i].path, code :permission[i].code });
			}
		}
		
		desktop.linksInit();
		desktop.onResize();	
	}
}