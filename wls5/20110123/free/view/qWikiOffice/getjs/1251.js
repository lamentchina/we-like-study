class_1251 = Ext.extend(Ext.app.Module, {
   id: 'id_1251',
   type: 'demo/tab',

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
                title: 'Tab Window',
                width: winWidth,
                height: winHeight,
                iconCls: 'tab-icon',
                shim: false,
                constrainHeader: true,
                layout: 'fit',
                items:[user_.getMyCenter('qd_u_mc')],
                taskbuttonTooltip: '<b>Tab Window</b><br />A window with tabs'
            });
        }
        win.show();
        user_.afterMyCenterAdded('qd_u_mc');
    }
});