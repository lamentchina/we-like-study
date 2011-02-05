class_13 = Ext.extend(Ext.app.Module, {
   id: 'id_13',

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
                title: '用户组',
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[obj.getList('qd_w_u_g_l')]
            });
        }
        win.show();
    }
});