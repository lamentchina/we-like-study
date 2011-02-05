class_12 = Ext.extend(Ext.app.Module, {
   id: 'id_12',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.usercenter,
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[user_.getMyCenter('qd_u_mc')],
            });
        }
        win.show();
        user_.afterMyCenterAdded('qd_u_mc');
    }
});