<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css"
	href="../../../libs/ext_3_2_1/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../libs/jquery-1.4.2.js"></script>
<script type="text/javascript"
	src="../../../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>
<script type="text/javascript" src="../../../libs/ext_3_2_1/ext-all.js"></script>
<script type="text/javascript" src="il8n.js"></script>
<script type="text/javascript" src="wls.js"></script>
<script type="text/javascript" src="user.js"></script>
<script type="text/javascript" src="user/group.js"></script>
<script type="text/javascript" src="user/privilege.js"></script>
<script type="text/javascript" src="quiz.js"></script>
<script type="text/javascript" src="quiz/paper.js"></script>
<script type="text/javascript" src="subject.js"></script>

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
    var tab = new Ext.TabPanel({
        id:'w_tp',
        activeTab: 0,
        frame:true,
        region:'center',       
        items:[	]
        });
	var tb_ = new Ext.Toolbar({
		id:"w_t",
		region: 'north',
		margins:'0 0 5 0',
        height: 28
	});

	var viewport = new Ext.Viewport({
		id:"w_v",
        layout: 'border',
        items: [tab,tb_]
	});

	Ext.Ajax.request({				
		method:'POST',				
		url:user_.config.AJAXPATH+"?controller=user&action=getMyMenu",				
		success:function(response){				
			var obj = jQuery.parseJSON(response.responseText);
			getToolBar(null,obj);
		},				
		failure:function(response){	
			
		},	
		params:{username:user_.myUser.username}				
	});

	var getToolBar = function(tb,obj){	
		if(tb==null){
			for(var i=0;i<obj.length;i++){
				
				var obj_ = {
					text:obj[i].text				
				};			

				if(typeof(obj[i].children)!='undefined'){
					var x = obj[i].children;
					var a = getToolBar('',x);
					if(a.length>0){
						obj_.menu = {}; 
						obj_.menu.items = a;
					}				
				}
				Ext.getCmp('w_t').add(obj_);
			}
			Ext.getCmp('w_t').doLayout();
		}else{
			var a = [];
			for(var i=0;i<obj.length;i++){
				var obj_ = null;
				if(obj[i].text=='slide'){						
					obj_ = '-';
					a.push(obj_);	
				}else if(obj[i].ismenu == true){
					obj_ = {
						menuType:obj[i].type,
						id_level:obj[i].id_level,
						text:obj[i].text,
						handler:menuClick
					};
					if(typeof(obj[i].children)!='undefined'){
						var x_ = obj[i].children;
						var a_ = getToolBar('',x_);
						if(a_.length>0){
							obj_.menu = {}; 
							obj_.menu.items = a_;
						}				
					}
					a.push(obj_);	
				}					
			}
			return a;
		}	
	}

	var menuClick = function(a){		
		if(a.menuType=='menu'){			
			if(a.id_level=='1150'){
				var o = new wls.quiz.paper();
				var list = o.getList('w_q_p_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_q_p_l');
			}else if(a.id_level=='1450'){
				var o = new wls.user();
				var list = o.getList('w_u_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_u_l');				
			}else if(a.id_level=='1350'){
				var o = new wls.user.group();
				var list = o.getList('w_u_g_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_u_g_l');	
			}else if(a.id_level=='1050'){
				var o = new wls.subject();
				var list = o.getList('w_s_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_s_l');	
			}else if(a.id_level=='1550'){
				var o = new wls.user.privilege();
				var list = o.getList('w_u_p_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_u_p_l');	
			}				
		}else if(a.menuType=='subject'){
			if(a.id_level=='11'){
				var o = new wls.quiz.paper();
				var list = o.getList('w_s_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_s_l');
			}
		}	
	}
});
</script>
</head>
<body>

</body>
</html>
