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


<script type="text/javascript">

<?php 
session_start();
echo "var user = '".$_SESSION['wls_user']['username']."';";
?>

Ext.onReady(function(){	
	var tb = new Ext.Toolbar({
		region: 'north',
		split: false,
        height: 28,
        collapsible: true,
        title: 'South'
	});
	tb.add( {
        text: 'Users',
        iconCls: 'user',
        menu: {
            xtype: 'menu',
            plain: true,
            items: {
                xtype: 'buttongroup',
                title: 'User options',
                autoWidth: true,
                columns: 2,
                defaults: {
                    xtype: 'button',
                    scale: 'large',
                    width: '100%',
                    iconAlign: 'left'
                },
                items: [{
                    text: 'User<br/>manager',
                    iconCls: 'edit'
                },{
                    iconCls: 'add',
                    width: 'auto',
                    tooltip: 'Add user'
                },{
                    colspan: 2,
                    text: 'Import',
                    scale: 'small'
                },{
                    colspan: 2,
                    text: 'Who is online?',
                    scale: 'small'
                }]
            }
        }
    });



	var l = new wls.user();
	var l2 = l.getList('w_u_l');
	l2.title = '用户列表';
    var tab = new Ext.TabPanel({
        
        activeTab: 0,
        frame:true,
        region:'center',
       
        items:[
			l2
        ]
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
		url:l.config.AJAXPATH+"?controller=user&action=getMyMenu",				
		success:function(response){				
			var obj = jQuery.parseJSON(response.responseText);
			getToolBar(null,obj);
		},				
		failure:function(response){	
			
		},	
		params:{username:user}				
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

				if(obj[i].ismenu == true){

					var obj_ = {
						text:obj[i].text
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
});



</script>
</head>
<body>

</body>
</html>
