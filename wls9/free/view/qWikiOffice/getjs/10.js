class_10 = Ext.extend(Ext.app.Module, {
   id: 'id_10',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        var obj = new wls.subject();
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			var obj = new wls.subject();
			
            win = desktop.createWindow({
                id: this.id,
                title: '科目',
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[obj.getList('qd_w_u_l')],
            });
        }
        win.show();
    }
});