class_1250 = Ext.extend(Ext.app.Module, {
   id: 'id_1250',
   type: 'demo/tab',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			var obj = new wls.quiz.wrong();
			
            win = desktop.createWindow({
                id: this.id,
                title: 'Tab Window',
                width: winWidth,
                height: winHeight,
                iconCls: 'tab-icon',
                shim: false,
                constrainHeader: true,
                layout: 'fit',
                items:[obj.getMyList('qd_w_q_w_ml')],
                taskbuttonTooltip: '<b>Tab Window</b><br />A window with tabs'
            });
        }
        win.show();
        user_.afterMyCenterAdded('qd_u_mc');
    }
});