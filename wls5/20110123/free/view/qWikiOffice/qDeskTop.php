<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="PRAGMA" content="NO-CACHE">
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
<meta http-equiv="EXPIRES" content="-1">
<title>A qWikiOffice Desktop</title>
<link rel="stylesheet" type="text/css"
	href="../libs/ext_3_2_1/resources/css/ext-all-notheme.css" />   

<script type="text/javascript" src="../libs/jquery-1.4.2.js"></script>
<script type="text/javascript" src="../libs/jqueryextend.js"></script>	
<script type="text/javascript"
	src="../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>
<script type="text/javascript" src="../libs/ext_3_2_1/ext-all.js"></script>

<!-- DESKTOP CSS -->
<link rel="stylesheet" type="text/css" href="free/view/qWikiOffice/resources/css/desktop.css" />

<!-- MODULES CSS -->
<!-- Dynamically generated based on the modules the member has access to -->
<style type="text/css">

.acc-icon { background-image: url(free/view/qWikiOffice/modules/demo/acc-win/client/resources/images/accordion16x16.gif) !important; }
.x-btn-medium .acc-icon { background-image: url(free/view/qWikiOffice/modules/demo/acc-win/client/resources/images/accordion24x24.gif) !important; }
.x-btn-large .acc-icon { background-image: url(free/view/qWikiOffice/modules/demo/acc-win/client/resources/images/accordion32x32.gif) !important; }


.qo-profile-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/profile/client/resources/images/icon16x16.png) !important; }
.x-btn-medium .qo-profile-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/profile/client/resources/images/icon24x24.png) !important; }
.x-btn-large .qo-profile-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/profile/client/resources/images/icon32x32.png) !important; }
.qo-profile-shortcut-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/profile/client/resources/images/icon48x48.png) !important; }
#qo-profile .x-panel-footer { background-color:transparent; border-top:0 none; }
#qo-profile .x-statusbar .x-status-error { background:transparent url(free/view/qWikiOffice/modules/qwiki/profile/client/resources/images/exclamation16x16.gif) no-repeat left center; color:#cc3333; cursor:pointer; padding-left:20px; }


/*
 * qWikiOffice Desktop 1.0
 * Copyright(c) 2007-2010, Murdock Technologies, Inc.
 * licensing@qwikioffice.com
 * 
 * http://www.qwikioffice.com/license
 */

.qo-pref-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/images/qo-pref-icon16x16.gif) !important; }
.x-btn-medium .qo-pref-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/images/qo-pref-icon24x24.gif) !important; }
.x-btn-large .qo-pref-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/images/qo-pref-icon32x32.gif) !important; }
.qo-pref-shortcut-icon { background-image: url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/images/qo-pref-icon48x48.png) !important; }

.prev-link-item-icon { float: left; }
.prev-link-item-txt { float: left; padding-top:10px; }
.pref-percent-field { background:#ffffff url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/images/percent-icon.gif) no-repeat right center; }
#qo-preferences .x-window-mc { color:#000 !important; }
#qo-preferences .x-border-layout-ct { background:transparent none; }
#qo-preferences .x-panel-header { background:transparent none; border-bottom:0px none; }
#qo-preferences .pref-card .x-panel-header { color:#000; }
#qo-preferences .pref-card-subpanel .x-panel-header { color:#000; }
#qo-preferences .x-grid-group-hd div { background:transparent url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesform-collapse-icon.gif) no-repeat scroll 3px 2px; padding:4px 4px 4px 23px; }
#qo-preferences .x-grid-group-collapsed .x-grid-group-hd div { background-position: 3px -33px; }
#pref-win-card-1 .x-panel-body li { margin:3px 3px 15px 3px; }
#pref-win-card-1 .x-panel-body li img { width:65px; height:55px; vertical-align:middle; margin-right:7px; margin-bottom:2px; }
#pref-win-card-1 .x-panel-body li a { text-decoration:none; color:#336699; font-weight:bold; }
#pref-win-card-1 .x-panel-body li span { padding-left:34px; }
#pref-win-card-1 .x-panel-body { padding:10px }
/*.pref-check-tree ul li { margin:0 3px; padding:3px 0; }
.pref-check-tree .x-tree-node .x-tree-selected { background-color:#c0c0c0; }
.pref-check-tree .x-tree-icon, .pref-check-tree .x-tree-ec-icon, .pref-check-tree .x-tree-elbow-line, .pref-check-tree .x-tree-elbow, .pref-check-tree .x-tree-elbow-end, .pref-check-tree .x-tree-elbow-plus, .pref-check-tree .x-tree-elbow-minus, .pref-check-tree .x-tree-elbow-end-plus, .pref-check-tree .x-tree-elbow-end-minus { width:3px; }
.pref-check-tree input.x-tree-node-cb { margin-left:5px; vertical-align:middle; }
.pref-check-tree .x-tree-node a span, .pref-check-tree .x-dd-drag-ghost a span { padding:1px 3px 1px 6px; }
*/
.pref-check-tree .x-tree-elbow, .pref-check-tree .x-tree-elbow-end { width:1px; }
.pref-check-tree .complete .x-tree-node-anchor span { color:#777; text-decoration:line-through; }
.icon-pref-shortcut { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesshortcut-icon.gif); }
.icon-pref-autorun { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesautorun-icon.gif); }
.icon-pref-quickstart { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesquickstart-icon.gif); }
.icon-pref-appearance { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesappearance-icon.gif); }
.icon-pref-wallpaper { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imageswallpaper-icon.gif); }
.pref-theme-groups .ux-explorerview-large-item { cursor:pointer; height:100px; margin:5px 0 0 5px; -moz-user-select:none; width:127px; }
.pref-theme-groups .ux-explorerview-large-item .ux-explorerview-icon { height:75px; width:127px; }
.pref-theme-groups .x-grid-group-hd, .pref-wallpaper-groups .x-grid-group-hd { border-bottom:1px solid #ccc; }
.pref-wallpaper-groups .ux-explorerview-large-item { cursor:pointer; height:100px; margin:5px 0 0 5px; -moz-user-select:none; width:127px; }
.pref-wallpaper-groups .ux-explorerview-large-item .ux-explorerview-icon { height: 75px; width: 127px; }
.pref-bg-pos-center { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesbg-center-icon.png); }
.pref-bg-pos-tile { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesbg-tile-icon.png); }
.pref-bg-color-icon { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesbg-color-icon.gif) !important; }
.pref-font-color-icon { background-image:url(free/view/qWikiOffice/modules/qwiki/preferences/client/resources/imagesfont-color-icon.gif) !important; }


</style>

<!-- CORE -->
<!-- In a production environment these would be minified into one file -->
<script type="text/javascript" src="free/view/qWikiOffice/client/App.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Desktop.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Module.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Notification.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Shortcut.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/StartMenu.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/TaskBar.js"></script>

<script type="text/javascript" src="free/view/il8n.js"></script>
<script type="text/javascript" src="free/view/wls.js"></script>
<script type="text/javascript" src="free/view/user.js"></script>
<script type="text/javascript" src="free/view/user/group.js"></script>
<script type="text/javascript" src="free/view/user/privilege.js"></script>
<script type="text/javascript" src="free/view/quiz.js"></script>
<script type="text/javascript" src="free/view/quiz/paper.js"></script>
<script type="text/javascript" src="free/view/quiz/wrong.js"></script>
<script type="text/javascript" src="free/view/quiz/log.js"></script>
<script type="text/javascript" src="free/view/subject.js"></script>

<script type="text/javascript" src="../libs/swfobject.js"></script>

<!-- QoDesk -->
<!-- This dynamic file will load all the modules the member has access to and setup the desktop -->
<script type="text/javascript">

 var user_ = new wls.user();
 </script>
 <?php 

 if(isset($_SESSION['wls_user']) && isset($_SESSION['wls_user']['id'])){	
 ?>
 <script type="text/javascript">
 <?php 
 	
 	echo "user_.myUser.privilege = '".$_SESSION['wls_user']['privilege']."';\n";
 	echo "user_.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
 	echo "user_.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
 	echo "user_.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
 	echo "user_.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
 	echo "user_.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
 	echo "user_.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
 	
 ?>
 
Ext.namespace('Ext.ux','QoDesk');

QoDesk.App = new Ext.app.App({
   init : function(){
      Ext.BLANK_IMAGE_URL = '../libs/ext_3_2_1/resources/images/default/s.gif';
      Ext.QuickTips.init();
   },

   /**
    * The member's name and group name for this session.
    */
   memberInfo: {
      name: 'Todd Murdock',
      group: 'System Administrator'
   },

   /**
    * An array of the module definitions.
    * The definitions are used until the module is loaded on demand.
    */
    modules: <?php echo json_encode($modules) ?> ,
          	


   /**
    * The desktop config object.
    */
   desktopConfig: {
      appearance: {
    	"fontColor":"333333",
    	"taskbarTransparency":50,
    	"theme":{
			"id":1,
			"name":"Blue",
			"file":"free\/view\/qWikiOffice\/resources\/css\/xtheme-blue.css"
		}
	  },
      background: {
	      "color":"f9f9f9",
	      "wallpaperPosition":"center",
	      "wallpaper":{
	      	"id":10,
	      	"name":"Lady Buggin",
	      	"file":"free\/view\/qWikiOffice\/resources\/wallpapers\/ladybuggin.jpg"
		   }
	   },
      launchers: {
		  "shortcut":<?php echo json_encode($shortcut)?>,
		  "quickstart":<?php echo json_encode($shortcut)?>
	 },
      taskbarConfig: {
         buttonScale: 'large',
         position: 'bottom',
         quickstartConfig: {
            width: 120
         },
         startButtonConfig: {
            iconCls: 'icon-qwikioffice',
            text: 'Start'
         },
         startMenuConfig: {
            iconCls: 'icon-user-48',
            title: 'Todd Murdock',
            width: 320
         }
      }
   }
});



<?php 
 }else{
 	?>
<link rel="stylesheet" type="text/css"
	href="../libs/ext_3_2_1/resources/css/xtheme-blue.css" />
<script type="text/javascript">
 	Ext.onReady(function(){

 		var copoment = user_.getLogin();
 	
 		var window = new Ext.Window({
 			title:il8n.WeLikeStudy,
 	        width: 300,
 	        height: 300,
 	        layout: 'fit',
 	        plain:true,
 	        bodyStyle:'padding:5px;',
 	        buttonAlign:'center',
 	        items: [copoment]       
 	    });
 	
 	    window.show();
 	});

 <?php 
 }
 ?>
 </script>
</head>
<body scroll="no"></body>
</html>
