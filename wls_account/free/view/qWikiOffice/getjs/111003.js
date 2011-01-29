class_111003 = Ext.extend(Ext.app.Module, {
   id: 'id_111003',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        var obj = new wls.subject();
        obj.id_level = '1003';
        
        var cmp = obj.getSubjectCenter('111003')
        if(!win){	
        	var winWidth = desktop.getWinWidth() / 1.1;
			var winHeight = desktop.getWinHeight() / 1.1;        	
            win = desktop.createWindow({
                id: this.id,
                title: '科目试卷',
                width: winWidth,
                height: winHeight,

                layout: 'fit',
                modal:true,
                items:[cmp]
            });
        }
        win.show();
        obj.getMyQuizLine('111003chart');
    }
});