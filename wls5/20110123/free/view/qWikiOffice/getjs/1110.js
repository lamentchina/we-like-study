class_1110 = Ext.extend(Ext.app.Module, {
   id: 'id_1110',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: '无数据',
                width: 300,
                height: 300,

                layout: 'fit',
                modal:true,
                items:[ new Ext.BoxComponent({
	                    html:'没有数据'
	            })]
            });
        }
        win.show();
    }
});