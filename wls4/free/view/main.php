<?php
session_start(); 
if(isset($_SESSION['wls_user'])){
	
}else{
	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- EXT JS LIBRARY -->
<!-- Using cachefly -->
<link rel="stylesheet" type="text/css"
	href="../../../libs/ext_3_2_1/resources/css/ext-all.css" />
<script type="text/javascript"
	src="../../../libs/jquery-1.4.2.js"></script>	
<script type="text/javascript"
	src="../../../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>

<script type="text/javascript" src="../../../libs/ext_3_2_1/ext-all.js"></script>


<!-- DESKTOP CSS -->
<link rel="stylesheet" type="text/css" href="../../../libs/qWikiOffice1/resources/css/desktop.css" />

<!-- CORE -->
<!-- In a production environment these would be minified into one file -->
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/App.js"></script>
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/Desktop.js"></script>
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/Module.js"></script>
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/Notification.js"></script>
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/Shortcut.js"></script>
<script type="text/javascript" src="../../../libs/qWikiOffice1/client/StartMenu.js"></script>

<script type="text/javascript" src="../../../libs/qWikiOffice1/client/TaskBar.js"></script>

<script type="text/javascript" src="il8n.js"></script>
<script type="text/javascript" src="wls.js"></script>
<script type="text/javascript" src="user.js"></script>

<!-- QoDesk -->
<!-- This dynamic file will load all the modules the member has access to and setup the desktop -->
<script >
/*
 * qWikiOffice Desktop 1.0
 * Copyright(c) 2007-2010, Murdock Technologies, Inc.
 * licensing@qwikioffice.com
 *
 * http://www.qwikioffice.com/license
 */

Ext.namespace('Ext.ux','QoDesk');


var q_w_user = new wls.user();


QoDesk.App = new Ext.app.App({
   init : function(){
      Ext.BLANK_IMAGE_URL = 'resources/images/default/s.gif';
      Ext.QuickTips.init();
   },

   /**
    * The member's name and group name for this session.
    */
   memberInfo: {
      name: 'Todd222 Murdock222',
      group: 'System Administrator'
   },

   /**
    * An array of the module definitions.
    * The definitions are used until the module is loaded on demand.
    */
   modules: [ {
	   "id":"demo-accordion",
	   "type":"demo/accordion",
	   "className":"QoDesk.AccordionWindow",
	   "launcher":{
			"iconCls":"acc-icon",
			"shortcutIconCls":"demo-acc-shortcut",
			"text":"Accordion Window",
			"tooltip":"<b>Accordion Window<\/b><br \/>A window with an accordion layout"
		}
   		,"launcherPaths":{
   	   		"startmenu":"\/"
   	   }
	},{
		"id":"qd_wls_user",
		"type":"demo/bogus",
		"className":"QoDesk.User",
		"launcher":{
			"iconCls":"bogus-icon",
			"shortcutIconCls":"demo-bogus-shortcut",
			"text":"aaaaaaaaaaaaaaa",
			"tooltip":"aaaaaaaaaaaaaaaaaaaaaaaa"
		},
		"launcherPaths":{
			"startmenu":"\/"
		}
	},{
		"id":"qo-preferences",
		"type":"system/preferences",
		"className":"QoDesk.QoPreferences",
		"launcher":{
			"iconCls":"qo-pref-icon",
			"shortcutIconCls":"qo-pref-shortcut-icon",
			"text":"QO Preferences",
			"tooltip":"<b>QO Preferences<\/b><br \/>Allows you to modify your desktop"
		},
		"launcherPaths":{
			"contextmenu":"\/",
			"startmenutool":"\/"
		}
	}],
	
   /**
    * The desktop config object.
    */
   desktopConfig: {
      appearance: {"fontColor":"333333","taskbarTransparency":100,"theme":{"id":1,"name":"Blue","file":"resources\/css\/xtheme-blue.css"}},
      background: {"color":"f9f9f9","wallpaperPosition":"center","wallpaper":{"id":7,"name":"Eos","file":"resources\/wallpapers\/eos.jpg"}},
      launchers: {"shortcut":["qd_wls_user","qo-preferences"],"quickstart":["qd_wls_user"]},
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
            title: 'Todd222 Murdock222',
            width: 320
         }
      }
   }
});
</script>
</head>
<body scroll="no"></body>
</html>
