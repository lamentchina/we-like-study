<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title><?php echo $this->c->siteName ?></title>
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->c->libsPath ?>ext_3_2_1/resources/css/ext-all-notheme.css" />   

<script type="text/javascript" src="<?php echo $this->c->libsPath ?>jquery-1.4.2.js"></script>
<script type="text/javascript" src="<?php echo $this->c->libsPath ?>jqueryextend.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>
<script type="text/javascript" src="<?php echo $this->c->libsPath ?>ext_3_2_1/ext-all.js"></script>
<script type="text/javascript" src="<?php echo $this->c->libsPath ?>ext_3_2_1/ext-lang-zh_CN.js"></script>

<link rel="stylesheet" type="text/css" href="free/view/qWikiOffice/resources/css/desktop.css" />

<style type="text/css">
.icon_user_48_48{
	background-image:url(free/view/images/user48x48.png) !important;
}
.icon_user_16_16{
	background-image:url(free/view/images/user16x16.png) !important;
}
.icon_layout_48_48{
	background-image:url(free/view/images/layout48x48.png) !important;
}
.icon_layout_16_16{
	background-image:url(free/view/images/layout16x16.gif) !important;
}
.icon_teacher_48_48{
	background-image:url(free/view/images/teacher48x48.png) !important;
}
.icon_teacher_16_16{
	background-image:url(free/view/images/teacher16x16.png) !important;
}
.icon_wrongbook_48_48{
	background-image:url(free/view/images/wrongbook48x48.png) !important;
}
.icon_wrongbook_16_16{
	background-image:url(free/view/images/wrongbook16x16.gif) !important;
}
.icon_paper_48_48{
	background-image:url(free/view/images/paper48x48.png) !important;
}
.icon_paper_16_16{
	background-image:url(free/view/images/paper16x16.png) !important;
}
.icon_key_48_48{
	background-image:url(free/view/images/key48x48.png) !important;
}
.icon_key_16_16{
	background-image:url(free/view/images/key16x16.gif) !important;
}
.icon_discuzx_48_48{
	background-image:url(free/view/images/discuzx48x48.png) !important;
}
.icon_discuzx_16_16{
	background-image:url(free/view/images/discuzx16x16.png) !important;
}
.icon_subject_48_48{
	background-image:url(free/view/images/subject48x48.png) !important;
}
.icon_subject_16_16{
	background-image:url(free/view/images/subject16x16.png) !important;
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

<script type="text/javascript" src="<?php echo $this->c->libsPath ?>swfobject.js"></script>

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
<script type="text/javascript" src="free/view/knowledge.js"></script>

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
      Ext.BLANK_IMAGE_URL = '<?php echo $this->c->libsPath ?>ext_3_2_1/resources/images/default/s.gif';
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
	      	"file":"free/view/qWikiOffice/resources/wallpapers/wallpaper2.jpg"
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
	href="<?php echo $this->c->libsPath ?>ext_3_2_1/resources/css/xtheme-blue.css" />
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
<body scroll="no">

</body>
</html>