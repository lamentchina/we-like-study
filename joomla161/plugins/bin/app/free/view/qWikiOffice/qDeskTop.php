<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title><?php echo $this->c->siteName ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->c->libsPath ?>ext_3_2_1/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->c->libsPath ?>ext_3_2_1/resources/css/<?php echo $this->c->theme ?>" />
<link rel="stylesheet" type="text/css" href="free/view/wls.css" />
<link rel="stylesheet" type="text/css" href="free/view/modules.css" />
<link rel="stylesheet" type="text/css" href="free/view/qWikiOffice/resources/css/desktop.css" />

<style type="text/css">
<?php //echo $css ?>
</style>
	
<script type="text/javascript" src="wls.php?controller=system&action=translateIniToJsClass"></script>
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/adapter/ext/ext-base.js"></script>
<!-- 	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/ext-all.js"></script>	
-->
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/ext-foundation.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/ext-dd.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/resizable.js"></script>
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/cmp-foundation.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/pkg-menu.js"></script>		
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/pkg-buttons.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/window.js"></script>	
<script type="text/javascript"
	src="<?php echo $this->c->libsPath ?>ext_3_2_1/pkgs/pkg-tips.js"></script>	


	
<!--qWikiOffice Js API  -->
<script type="text/javascript" src="free/view/qWikiOffice/client/App.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Desktop.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Module.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Notification.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/Shortcut.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/StartMenu.js"></script>
<script type="text/javascript" src="free/view/qWikiOffice/client/TaskBar.js"></script>

<script type="text/javascript" src="free/view/wls.js"></script>
<script type="text/javascript" src="free/view/system.js"></script>
<script type="text/javascript" src="free/view/user.js"></script>

<script type="text/javascript">
var me = new wls.user();
</script>
<?php 
if(isset($_SESSION['wls_user']) && isset($_SESSION['wls_user']['id'])){	
?>
<script type="text/javascript">
<?php 
echo "me.myUser.access = '".$_SESSION['wls_user']['access']."';\n";
echo "me.myUser.access2 = ".json_encode($_SESSION['wls_user']['access2']).";\n";
echo "me.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
echo "me.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
echo "me.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
echo "me.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
echo "me.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
echo "me.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
?>
var modules = <?php echo ($modules) ?>;
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
      name: me.myUser.username
   },

   /**
    * An array of the module definitions.
    * The definitions are used until the module is loaded on demand.
    */
    modules: modules ,

   /**
    * The desktop config object.
    */
   desktopConfig: {
      appearance: {
    	"fontColor":"333333",
    	"taskbarTransparency":70
	  },
      background: {
	      "color":"f9f9f9",
	      "wallpaperPosition":"center",
	      "wallpaper":{
	      	"id":10,
	      	"name":"WLS",
	      	"file":"<?php echo $this->c->background ?>"
		   }
	   },
      launchers: {
		  "shortcut":<?php echo ($shortcut)?>,
		  "quickstart":<?php echo ($quickstart)?>
		},
      taskbarConfig: {
         buttonScale: 'large',
         position: 'bottom',
         quickstartConfig: {
            width: 60
         },

         startMenuConfig: {
            iconCls: 'icon-user-48',
            title: me.myUser.username,
            width: 320
         }
      }
   }
});

<?php 
 }else{
?>

<script type="text/javascript">
 alert('<?php echo $this->c->lang['installError']; ?>');
 <?php 
 }
 ?>
 </script>
</head>
<body scroll="no">
</body>
</html>