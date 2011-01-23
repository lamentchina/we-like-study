<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="PRAGMA" content="NO-CACHE">
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
<meta http-equiv="EXPIRES" content="-1">

<title>A qWikiOffice Desktop</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>ExtTop - Desktop Sample App</title>
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
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/demo/acc-win/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/demo/bogus-win/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/demo/grid-win/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/demo/layout-win/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/demo/tab-win/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/common/libraries/column-tree/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/qwiki/admin/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/common/libraries/color-picker/resources/styles.css' />

<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/common/libraries/explorer-view/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/qwiki/preferences/client/resources/styles.css' />
<link rel='stylesheet' type='text/css' href='free/view/qWikiOffice/modules/qwiki/profile/client/resources/styles.css' />

<!-- CORE -->
<!-- In a production environment these would be minified into one file -->
<script type="text/javascript" src="free/view/qWikiOffice/client/App.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Desktop.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Module.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Notification.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Shortcut.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/StartMenu.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/TaskBar.js"></script>



<!-- QoDesk -->
<!-- This dynamic file will load all the modules the member has access to and setup the desktop -->
<script type="text/javascript">
/*
 * qWikiOffice Desktop 1.0
 * Copyright(c) 2007-2010, Murdock Technologies, Inc.
 * licensing@qwikioffice.com
 *
 * http://www.qwikioffice.com/license
 */

var qtest = {
	createWindow:function(){
		
	}	
	,launcher:function(){
		return true;
	}
};
 
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
    modules: [ 
          	{
          		"id":"qtest",
          		"type":"demo/accordion",
          		"className":"qtest",
          		"launcher":{
          			"iconCls":"acc-icon",
          			"shortcutIconCls":"demo-acc-shortcut",
          			"text":"Accordion Window",
          			"tooltip":"<b>Accordion Window<\/b><br \/>A window with an accordion layout"
          		},
          		"launcherPaths":{
          			"startmenu":"\/"
          		},
          		"loaded":"true"
          	},
          	{
          		"id":"demo-bogus",
          		"type":"demo/bogus",
          		"className":"QoDesk.BogusWindow"
          		,"launcher":{
          			"iconCls":"bogus-icon",
          			"shortcutIconCls":"demo-bogus-shortcut",
          			"text":"Bogus Window",
          			"tooltip":"<b>Bogus Window<\/b><br \/>A bogus window"
          		},
          		"launcherPaths":{
          			"startmenu":"\/Bogus Menu\/Bogus Sub Menu"
          		}
          	},
          	{
          		"id":"demo-grid",
          		"type":"demo/grid",
          		"className":"QoDesk.GridWindow",
          		"launcher":{
          			"iconCls":"grid-icon",
          			"shortcutIconCls":"demo-grid-shortcut",
          			"text":"Grid Window",
          			"tooltip":"<b>Grid Window<\/b><br \/>A grid window"
          		},
          		"launcherPaths":{
          			"startmenu":"\/"
          		}
          	},
          	{
          		"id":"demo-layout",
          		"type":"demo/layout",
          		"className":"QoDesk.LayoutWindow",
          		"launcher":{
          			"iconCls":"layout-icon",
          			"shortcutIconCls":"demo-layout-shortcut",
          			"text":"Layout Window",
          			"tooltip":"<b>Layout Window<\/b><br \/>A layout window"
          		},
          		"launcherPaths":{
          			"startmenu":"\/"
          		}
          	},{
          		"id":"demo-tab",
          		"type":"demo/tab",
          		"className":"QoDesk.TabWindow",
          		"launcher":{
          			"iconCls":"tab-icon",
          			"shortcutIconCls":"demo-tab-shortcut",
          			"text":"Tab Window",
          			"tooltip":"<b>Tab Window<\/b><br \/>A tab window"
          		},
          		"launcherPaths":{
          			"startmenu":"\/"
          		}
          	},{
          		"id":"qo-admin",
          		"type":"system/administration",
          		"className":"QoDesk.QoAdmin",
          		"launcher":{
          			"iconCls":"qo-admin-icon",
          			"shortcutIconCls":"qo-admin-shortcut-icon",
          			"text":"QO Admin"
          			,"tooltip":"<b>QO Admin<\/b><br \/>Allows system administration"
          		},
          		"launcherPaths":{
          			"startmenu":"\/Admin"
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
          			"contextmenu":"\/","startmenutool":"\/"
          		}
          	},{
          		"id":"qo-profile",
          		"type":"user/profile",
          		"className":"QoDesk.QoProfile",
          		"launcher":{
          			"iconCls":"qo-profile-icon",
          			"shortcutIconCls":"qo-profile-shortcut-icon",
          			"text":"Profile",
          			"tooltip":"<b>Profile<\/b><br \/>Allows user profile administration"
          		},
          		"launcherPaths":{
          			"contextmenu":"\/",
          			"startmenutool":"\/"
          		}
          	} 
          	],


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
		  "shortcut":[
		      "qo-preferences",
		      "qo-admin",
		      "demo-accordion",
		      "demo-tab",
		      "demo-bogus"
		  ],
		  "quickstart":[
		      "qo-preferences",
		      "qo-admin",
		      "demo-tab"
		  ]
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

</script>
</head>
<body scroll="no"></body>
</html>
