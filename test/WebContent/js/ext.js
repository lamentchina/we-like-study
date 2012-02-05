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
        fields : ['id','age', 'birthday', 'name','gender','ismarried','cellphone']
    });
   
    store.load();

    var grid = new Ext.grid.GridPanel({
        store : store,
        columns :  [{
			header : '&nbsp;',
			width : 40,
			dataIndex : 'id'
        }, {
            header : '姓名',
            dataIndex : 'name'
        }, {
            header : '年龄',
            dataIndex : 'age'
        }, {
            header : '生日',
            dataIndex : 'birthday'
        }, {
            header : '性别',
            dataIndex : 'gender',
            renderer : function(a,b,c){
            	if(a==1){
            		return '男';
            	}else {
            		return '女';
            	}
            }
        }, {
            header : '婚姻',
            dataIndex : 'ismarried',
            renderer : function(a,b,c){
            	if(a==1){
            		return '已婚';
            	}else {
            		return '未婚';
            	}
            }
        }, {
            header : '手机号码',
            dataIndex : 'cellphone'
        }],
        width : "100%",
        //height : 400,
        bbar : new Ext.PagingToolbar({
            store : store,
            pageSize : 15,
            displayInfo : true
        })
    });

    grid.render(Ext.getBody());
}