<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css"
	href="../../../libs/ext_3_2_1/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../libs/jquery-1.4.2.js"></script>
<script type="text/javascript" src="../../../libs/jqueryextend.js"></script>	
<script type="text/javascript"
	src="../../../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js"></script>
<script type="text/javascript" src="../../../libs/ext_3_2_1/ext-all.js"></script>
<script type="text/javascript" src="../../../libs/ext_3_2_1/src/locale/ext-lang-zh_CN.js"></script>
<script type="text/javascript" src="../../../libs/swfobject.js"></script>
<script type="text/javascript" src="il8n.js"></script>
<script type="text/javascript" src="wls.js"></script>
<script type="text/javascript" src="user.js"></script>
<script type="text/javascript" src="user/group.js"></script>
<script type="text/javascript" src="user/privilege.js"></script>
<script type="text/javascript" src="quiz.js"></script>
<script type="text/javascript" src="quiz/paper.js"></script>
<script type="text/javascript" src="quiz/wrong.js"></script>
<script type="text/javascript" src="quiz/log.js"></script>
<script type="text/javascript" src="subject.js"></script>

<script type="text/javascript">
var user_ = new wls.user();
<?php 
session_start();
if(isset($_SESSION['wls_user']) && isset($_SESSION['wls_user']['id'])){	
	
	echo "user_.myUser.privilege = '".$_SESSION['wls_user']['privilege']."';\n";
	echo "user_.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
	echo "user_.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
	echo "user_.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
	echo "user_.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
	echo "user_.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
	echo "user_.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
	
?>

Ext.onReady(function(){		
    var tab = new Ext.TabPanel({
        id:'w_tp',
        activeTab: 0,
        frame:true,
        region:'center',       
        items:[]
    });
	var tb_ = new Ext.Toolbar({
		id:"w_t",
		region: 'north',
		 height: 28,
		margins:'0 0 5 0'
	});

	var viewport = new Ext.Viewport({
		id:"w_v",
        layout: 'border',
        autoDestroy :true,
        items: [tab,tb_]         
	});

	Ext.Ajax.request({				
		method:'POST',				
		url:user_.config.AJAXPATH+"?controller=user&action=getMyMenu",				
		success:function(response){				
			var obj = jQuery.parseJSON(response.responseText);
			getToolBar(null,obj);
			var cmp = user_.getMyCenter('w_u_c');
			cmp.title = '个人统计中心';
			cmp.closable = true;
			Ext.getCmp('w_tp').add(cmp);
			Ext.getCmp('w_tp').setActiveTab('w_u_c');	
			user_.afterMyCenterAdded('w_u_c');
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
//			console.debug(a.id_level);		
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
			}else if(a.id_level=='1153'){
				var o = new wls.quiz.wrong();
				var list = o.getList('w_q_w_l');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_q_w_l');	
			}else if(a.id_level=='1250'){
				var o = new wls.quiz.wrong();
				var list = o.getMyList('w_q_w_ml');
				list.closable=true;				
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_q_w_ml');	
			}else if(a.id_level=='1252'){
				user_.logOut();
			}else if(a.id_level=='1151'){				
				var o = new wls.quiz.log();
				var list = o.getList('w_q_lg_l');
				list.closable=true;		
				list.title = il8n.Log;		
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_q_lg_l');	
			}else if(a.id_level=='1253'){				
				var o = new wls.quiz.log();
				var list = o.getMyList('w_q_lg_ml');
				list.closable=true;		
				list.title = il8n.Log;		
				Ext.getCmp('w_tp').add(list);
				Ext.getCmp('w_tp').setActiveTab('w_q_lg_ml');	
			}else if(a.id_level=='1251'){	
				var cmp = Ext.getCmp('w_u_c');
				//console.debug();
				if(!cmp){
					var cmp = user_.getMyCenter('w_u_c');
					cmp.title = '个人统计中心';
					cmp.closable = true;
					Ext.getCmp('w_tp').add(cmp);
					Ext.getCmp('w_tp').setActiveTab('w_u_c');	
					user_.afterMyCenterAdded('w_u_c');
				}else{
					Ext.getCmp('w_tp').setActiveTab('w_u_c');	
				}	
				
//				if(!cmp){
//					
//				}else{
//					Ext.getCmp('w_tp').add(cmp);
//				}
//				console.debug(cmp);
				
			}								
		}else if(a.menuType=='subject'){
			var obj = new wls.subject();
			obj.id_level = a.id_level;
			
			var copoment = obj.getSubjectCenter('w_s_c'+obj.id_level);
			copoment.closable=true;			
			copoment.title = il8n.Subject+' '+a.text;	
			Ext.getCmp('w_tp').add(copoment);
			Ext.getCmp('w_tp').setActiveTab('w_s_c'+obj.id_level);	
			obj.getMyQuizLine('w_s_c'+obj.id_level+'chart');
		}	
	}
});

<?php 
}else{
	?>
	Ext.onReady(function(){

		var copoment = user_.getLogin();
	
		var window = new Ext.Window({
			title:il8n.WeLikeStudy,
	        width: 250,
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
<body>

</body>
</html>
