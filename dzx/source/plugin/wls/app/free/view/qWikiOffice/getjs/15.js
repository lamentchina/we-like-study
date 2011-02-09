class_15 = Ext.extend(Ext.app.Module, {
   id: 'id_15',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
    	var winWidth = desktop.getWinWidth() / 1.1;
		var winHeight = desktop.getWinHeight() / 1.1;
        
		var obj = new wls.user.privilege();
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.privilege,
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[obj.getList('qd_w_u_p_l')]
            });
        }
        win.show();
    }
});