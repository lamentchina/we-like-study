<?php 
header("Content-type: text/html; charset=utf-8");
$actionid = explode("_",$_REQUEST['moduleId']);
$actionid = $actionid[1];
//if($actionid)
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