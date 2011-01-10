QoDesk.User = Ext.extend(Ext.app.Module, {
	id: 'qd_wls_user',
	type: 'wls',
	loaded:true,
	
	init : function(){
	
	},
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			
			var list = q_w_user.getList();
            win = desktop.createWindow({
                id: this.id,
                title: 'Tab Window',
                width: winWidth,
                height: winHeight,
                iconCls: 'tab-icon',
                shim: false,
                constrainHeader: true,
                layout: 'fit',
                items:[list]
			});
        }
        win.show();
	}
});	