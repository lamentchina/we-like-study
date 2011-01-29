class_19 = Ext.extend(Ext.app.Module, {
   id: 'id_19',

   init : function(){

   },
	
	createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);
        
        if(!win){			
            win = desktop.createWindow({
                id: this.id,
                title: '教师中心',
                width: 300,
                height: 250,
                iconCls: 'tab-icon',
                shim: false,
                constrainHeader: true,
                layout: 'fit',
                modal:true,
                items:[new Ext.Button({text:'功能尚未完成'})],
                taskbuttonTooltip: '<b>教师中心</b>'
            });
        }
        win.show();
    }
});