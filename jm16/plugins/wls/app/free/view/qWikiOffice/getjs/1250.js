class_1250 = Ext.extend(Ext.app.Module, {
   id: 'id_1250',

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
                title: il8n.Quiz_Wrongs,
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                items:[obj.getMyList('qd_w_q_w_ml')],
            });
        }
        win.show();
    }
});