class_14 = Ext.extend(Ext.app.Module, {
   id: 'id_14',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
    	var winWidth = desktop.getWinWidth() / 1.1;
		var winHeight = desktop.getWinHeight() / 1.1;
		
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.user,
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[user_.getList('qd_w_u_l')]
            });
        }
        win.show();
    }
});