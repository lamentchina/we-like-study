<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title><?php echo $title;?></title>

<?php echo $head ?>

<!--[if IE]>
<link href="themes/css/ieHack.css" rel="stylesheet" type="text/css" />
<![endif]-->



<script type="text/javascript">
$(function(){	
	DWZ.init("dwz.frag.xml", {
//		loginUrl:"loginsub.html", loginTitle:"登录",	// 弹出登录对话框
		loginUrl:"login.html",	// 跳到登录页面
		debug:false,	// 调试模式 【true|false】
		callback:function(){
			initEnv();
			$("#themeList").theme({themeBase:"libs/DWZ/themes"});
			navTab.openTab('main', '我的主页', 'wls.php?controller=quiz_record&action=getChart', 'user.getChart("w_q_r_c")');
		}
	});
});
//清理浏览器内存,只对IE起效,FF不需要
if ($.browser.msie) {
	window.setInterval("CollectGarbage();", 10000);
}
</script>
</head>
<body scroll="no">
	<div id="layout">
		<div id="header">
			<div class="headerNav">

				<a class="logo" href="javascript:void(0)">标志</a>
				<ul class="nav">
					<!--  
					<li id="switchEnvBox"><a href="javascript:">（<span>北京</span>）切换角色</a>
						<ul>		
							<li><a href="sidebar_bj.html">北京</a></li>
							<li><a href="sidebar_sh.html">上海</a></li>

							<li><a href="sidebar_sh.html">南京</a></li>
							<li><a href="sidebar_sh.html">深圳</a></li>
							<li><a href="sidebar_sh.html">广州</a></li>
							<li><a href="sidebar_sh.html">天津</a></li>
							<li><a href="sidebar_sh.html">杭州</a></li>
						</ul>

					</li>
					<li><a href="changepwd.html" target="dialog">设置</a></li>
					-->
					<!--<li><a href="javascript:void(0)">反馈</a></li>-->
					<li><a href="/" target="_blank">论坛</a></li>
					<!--<li><a href="login.html">退出</a></li>-->
				</ul>
				<ul class="themeList" id="themeList">

					<li theme="default"><div class="selected">蓝色</div></li>
					<li theme="green"><div>绿色</div></li>
					<!--<li theme="red"><div>红色</div></li>-->
					<li theme="purple"><div>紫色</div></li>
					<li theme="silver"><div>银色</div></li>
				</ul>
			</div>

		</div>
		
		<div id="leftside">
			<div id="sidebar_s">
				<div class="collapse">
					<div class="toggleCollapse"><div></div></div>
				</div>
			</div>
			<div id="sidebar">
				<div class="toggleCollapse"><h2>主菜单</h2><div>收缩</div></div>
				<div class="accordion" fillSpace="sideBar">
					<div class="accordionHeader">
						<h2><span>Folder</span>试题库</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree treeFolder">
							<li><a href="wls.php?controller=quiz_paper_paper&action=getDWZlist" target="navTab">所有试卷</a>
								<ul>
								<?php 
									for($i=0;$i<count($quiztypeList['rows']);$i++){
										echo "<li><a href=\"wls.php?controller=quiz_paper_paper&action=getDWZlist&search={'id_quiz_type':'".$quiztypeList['rows'][$i]['id']."'}\" target=\"navTab\" rel=\"page".$quiztypeList['rows'][$i]['id']."\">".$quiztypeList['rows'][$i]['title']."</a></li>";
									}
								?>

								</ul>
							</li>
							<li><a href="wls.php?controller=quiz_type&action=getDWZlist" target="navTab" rel="quiz_type">考试科目</a></li>
							<!--  
							<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_panel">日常练习</a></li>
							<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_validation">特训考场 </a>
								<ul>
									<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_validation">闯关训练</a></li>
									<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_datepicker">抢答练习</a></li>
									<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_button">模拟考场</a></li>
									<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="w_textInput">考场说明</a></li>
								</ul>
							</li>
							-->
						</ul>
					</div>
					<!-- 
					<div class="accordionHeader">
						<h2><span>Folder</span>我的网校课程</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree treeFolder">
							<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="demo_page1">未开通</a></li>
							<li><a href="wls.php?controller=quiz_quiz&action=undo" target="navTab" rel="demo_page1">正在开发中</a></li>
					</div>
 					-->
					<div class="accordionHeader">
						<h2><span>Folder</span>个人中心</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="wls.php?controller=quiz_record&action=getList&returnType=dwz" target="navTab" rel="dlg_page">历次考试记录</a></li>
							<li><a href="wls.php?controller=quiz_wrongs&action=getDWZlist" target="navTab" rel="dlg_page">错题集</a></li>
						</ul>
					</div>
					
					<?php
					$arr = explode(",",$userinfo['id_group']);
					if(in_array($this->cfg->group_admin,$arr)){
					?>
					<div class="accordionHeader">
						<h2><span>Folder</span>管理员中心</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="wls.php?controller=user&action=getDWZlist" target="navTab" rel="userlist">查看用户</a></li>						
						</ul>
					</div>
					<?php 
					}
					?>
					
					<div class="accordionHeader">
						<h2><span>Folder</span>帮助</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="wls.php?controller=quiz_quiz&action=aboutplugin" target="dialog" rel="dlg_page1">插件说明</a></li>
							<li><a href="wls.php?controller=quiz_quiz&action=authorInfo" target="dialog" rel="dlg_page2">联系作者</a></li>
							<li><a href="wls.php?controller=quiz_quiz&action=commercial" target="dialog" rel="dlg_page3">商业合作</a></li>							
						</ul>
					</div>
				</div>
				
			</div>

		</div>
		<div id="container">
			<div id="navTab" class="tabsPage">
				<div class="tabsPageHeader">
					<div class="tabsPageHeaderContent"><!-- 显示左右控制时添加 class="tabsPageHeaderMargin" -->
						<ul class="navTab-tab">
							<li tabid="main" class="main"><a href="javascript:void(0)"><span><span class="home_icon">我的主页</span></span></a></li>
						</ul>

					</div>
					<div class="tabsLeft">left</div><!-- 禁用只需要添加一个样式 class="tabsLeft tabsLeftDisabled" -->
					<div class="tabsRight">right</div><!-- 禁用只需要添加一个样式 class="tabsRight tabsRightDisabled" -->
					<div class="tabsMore">more</div>
				</div>
				<ul class="tabsMoreList">
					<li><a href="javascript:void(0)">我的主页</a></li>
				</ul>
				<div class="navTab-panel tabsPageContent">
					<div>
					<b>我们喜欢学习!在线考试学习系统</b>

					</div>
				</div>
			</div>
		</div>

		<div id="taskbar" style="left:0px; display:none;">
			<div class="taskbarContent">
				<ul></ul>

			</div>
			<div class="taskbarLeft taskbarLeftDisabled" style="display:none;">taskbarLeft</div>
			<div class="taskbarRight" style="display:none;">taskbarRight</div>
		</div>
		<div id="splitBar"></div>
		<div id="splitBarProxy"></div>
	</div>
	
	<div id="footer">
		
		<?php 
			include_once 'view/foot.php';
		?>
	</div>

<!--拖动效果-->
	<div class="resizable"></div>
<!--阴影-->
	<div class="shadow" style="width:508px; top:148px; left:296px;">
		<div class="shadow_h">
			<div class="shadow_h_l"></div>
			<div class="shadow_h_r"></div>
			<div class="shadow_h_c"></div>
		</div>

		<div class="shadow_c">
			<div class="shadow_c_l" style="height:296px;"></div>
			<div class="shadow_c_r" style="height:296px;"></div>
			<div class="shadow_c_c" style="height:296px;"></div>
		</div>
		<div class="shadow_f">
			<div class="shadow_f_l"></div>
			<div class="shadow_f_r"></div>
			<div class="shadow_f_c"></div>

		</div>
	</div>
	<!--遮盖屏幕-->
	<div id="alertBackground" class="alertBackground"></div>
	<div id="dialogBackground" class="dialogBackground"></div>

	<div id='background' class='background'></div>
	<div id='progressBar' class='progressBar'>数据加载中，请稍等...</div>
</body>
</html>
