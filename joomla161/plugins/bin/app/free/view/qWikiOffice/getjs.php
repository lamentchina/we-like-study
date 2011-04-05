<?php 
header("Content-type: text/html; charset=utf-8");
$actionid = explode("_",$_REQUEST['moduleId']);
$actionid = $actionid[1];
if(strlen($actionid)>2 && substr($actionid,0,2)=='11'){
	$str = "
class_".$actionid." = Ext.extend(Ext.app.Module, {
   id: 'id_".$actionid."',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
    	var winWidth = desktop.getWinWidth() / 1.1;

        
		//var obj = new wls.subject();
		//obj.id_level = '".substr($actionid,2)."';
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.paper+'_'+".substr($actionid,2).",
                width: winWidth,
                height: 530,
				iconCls : 'icon_paper_16_16',
				iconClsGhostBar : 'icon_paper_32_32',
                layout: 'fit',
				listeners : {
					'show':function(x){
						var c = document.getElementById('spList_".$actionid."');   
						c.src =  me.config.AJAXPATH + \"?controller=subject&action=viewGetSubjectCenter&actionid=".$actionid."\";
					}
				},
				html : \"<iframe id='spList_".$actionid."' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />\"
                //items:[ obj.getSubjectCenter('qd_w_s_c_".$actionid."')]
            });
        }
        win.show();
        //obj.getMyQuizLine('qd_w_s_c_".$actionid."chart');
    }
});	
	";
	echo $str;
	exit();
}
if(file_exists('getjs/'.$actionid.".js")){
	include_once 'getjs/'.$actionid.".js";
}else{
	$str = "
class_".$actionid." = Ext.extend(Ext.app.Module, {
   id: 'id_".$actionid."',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
    	var winWidth = desktop.getWinWidth() / 2.1;
		var winHeight = desktop.getWinHeight() / 2.1;
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.unDone,
                width: winWidth,
                height: winHeight,
				modal:true,
                layout: 'fit',
                items:[ new Ext.BoxComponent({
	                    html:'//TODO '+il8n.unDone
	            })]
            });
        }
        win.show();
    }
});	
	";
	echo $str;
}

?>