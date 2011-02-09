class_1908 = Ext.extend(Ext.app.Module, {
   id: 'id_1908',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        var obj = new wls.subject();
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			var obj = new wls.knowledge();
			
            win = desktop.createWindow({
                id: this.id,
                title: il8n.knowledge,
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[obj.getList('qd_w_k_l')],
            });
        }
        win.show();
    }
});