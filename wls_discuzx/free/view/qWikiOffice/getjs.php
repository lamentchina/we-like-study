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
		var winHeight = desktop.getWinHeight() / 1.1;
        
		var obj = new wls.subject();
		obj.id_level = '".substr($actionid,2)."';
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: '科目试卷',
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[ obj.getSubjectCenter('qd_w_s_c')]
            });
        }
        win.show();
        obj.getMyQuizLine('qd_w_s_cchart');
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
    	var winWidth = desktop.getWinWidth() / 1.1;
		var winHeight = desktop.getWinHeight() / 1.1;
        
		var obj = new wls.user.group();
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: '尚未完成',
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[ new Ext.BoxComponent({
	                    html:'//TODO 此功能尚未完成'
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