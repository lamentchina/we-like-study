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


.icon_user_32_32{
	background-image:url(free/view/images/user32x32.png);
}
.icon_user_16_16{
	background-image:url(free/view/images/user16x16.png) !important;
}
.icon_layout_48_48{
	background-image:url(free/view/images/layout48x48.png) !important;
}
.icon_layout_32_32{
	background-image:url(free/view/images/layout32x32.gif);
}
.icon_layout_16_16{
	background-image:url(free/view/images/layout16x16.gif) !important;
}
</style>

<!--qWikiOffice Js API  -->
<script type="text/javascript" src="free/view/qWikiOffice/client/App.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Desktop.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Module.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Notification.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Shortcut.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/StartMenu.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/TaskBar.js"></script>

<script type="text/javascript" src="../libs/swfobject.js"></script>

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
      name: user_.myUser.username,
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
		  "quickstart":<?php echo json_encode($quickstart)?>
	 },
      taskbarConfig: {
         buttonScale: 'large',
         position: 'bottom',
         quickstartConfig: {
            width: 60
         },
         startButtonConfig: {
            iconCls: 'icon-qwikioffice',
            text: 'Start'
         },
         startMenuConfig: {
            iconCls: 'icon-user-48',
            title: user_.myUser.username,
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
