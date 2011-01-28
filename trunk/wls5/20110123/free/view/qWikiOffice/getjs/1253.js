class_1253 = Ext.extend(Ext.app.Module, {
   id: 'id_1253',


   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        var obj = new wls.quiz.log();
        
        if(!win){
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;
			
            win = desktop.createWindow({
                id: this.id,
                title: '练习记录',
                width: winWidth,
                height: winHeight,
                
                layout: 'fit',
                items:[obj.getMyList('qd_w_q_l_ml')],
                taskbuttonTooltip: '<b>Tab Window</b><br />A window with tabs'
            });
        }
        win.show();
    }
});