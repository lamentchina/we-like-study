<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
<link rel="stylesheet" type="text/css"
	href="../../../../libs/ext_3_2_1/resources/css/ext-all.css" />
<script type="text/javascript"
	src="../../../../libs/jquery-1.4.2.js"></script>	
<script type="text/javascript"
	src="../../../../libs/jqueryextend.js"></script>		
<script type="text/javascript"
	src="../../../../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>
<!--  
<script type="text/javascript"
	src="../../../../libs/ext_3_2_1/adapter/ext/ext-base.js"></script>	
-->
<script type="text/javascript"
	src="../../../../libs/ext_3_2_1/ext-all.js"></script>
<script type="text/javascript" src="../il8n.js"></script>
<script type="text/javascript" src="../wls.js"></script>
<script type="text/javascript" src="../user.js"></script>
<script type="text/javascript" src="../quiz.js"></script>
<script type="text/javascript" src="../quiz/paper.js"></script>
<script type="text/javascript" src="../quiz/log.js"></script>

<script type="text/javascript">
var user_ = new wls.user();
<?php 
session_start();
if(isset($_SESSION['wls_user'])){	
	echo "user_.myUser.privilege = '".$_SESSION['wls_user']['prvilege']."';\n";
	echo "user_.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
	echo "user_.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
	echo "user_.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
	echo "user_.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
	echo "user_.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
}else{
	echo "not login!";
}
?>
Ext.onReady(function(){
	var obj = new wls.quiz.log();

	obj.id_level = '1001';
	var copoment = obj.getList('domid');
	var window = new Ext.Window({
        width: "80%",
        height: 500,
        minWidth: 300,
        minHeight: 200,
        layout: 'fit',
        plain:true,
        bodyStyle:'padding:5px;',
        buttonAlign:'center',
        items: [copoment]       
    });

    window.show();

});
</script>
</head>
<body>

</body>
</html>