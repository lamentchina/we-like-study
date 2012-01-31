var getGrid = function(){
    var store = new Ext.data.JsonStore({
        autoDestroy : true,
        proxy: {
            type: 'ajax',
            url: '/test/Server.do?class=com.wei1224hf.myapp.Person&function=list',
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'id'
            }
        },
        fields : ['id', 'birthday', 'name','gender']
    });
   
    store.load();

    var grid = new Ext.grid.GridPanel({
        store : store,
        columns :  [{
			header : '&nbsp;',
			width : 40,
			dataIndex : 'id'
        }, {
            header : 'name',
            dataIndex : 'name'
        }],
        width : "100%",
        height : 400,
        bbar : new Ext.PagingToolbar({
            store : store,
            pageSize : 15,
            displayInfo : true
        })
    });

    grid.render(Ext.getBody());
}