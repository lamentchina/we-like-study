Ext.ns('App');



App.Scheduler = {
    
    // Initialize application
    init : function(serverCfg) {  
        Ext.QuickTips.init();      
        this.grid = this.createGrid();
        
        this.initEvents();
    },
    
    initEvents : function() {
        var g = this.grid;
        
        g.on({
            'timeheaderdblclick' : this.onTimeHeaderDoubleClick,
            scope : this
        });
    },
    
    onTimeHeaderDoubleClick : function(g, start, end, e) {
        var days = Math.round(Date.getDurationInDays(start, end));
        
        if (days === 7) {
            g.setView(start, end, 'weekAndDays', Sch.ViewBehaviour.WeekView);
        } else {
            g.setView(start, end, 'monthAndQuarters', Sch.ViewBehaviour.MonthView);
        }
    },
    
    createGrid : function() {
        var start = new Date(2010,7,20),
            end = start.add(Date.MONTH, 10);
       
        var store = new Ext.ux.maximgb.tg.AdjacencyListStore({
            defaultExpanded : false,
    	    autoLoad : true,
            proxy : new Ext.data.HttpProxy({
                url : 'tasks.xml',
                method:'GET'
            }),
		   reader: new Ext.data.XmlReader({
                // records will have a 'Task' tag
                record: 'Task',
                idProperty: "Id",
                fields : [
                    // Mandatory fields
     	            {name:'Id', type : 'int'},
                    {name:'Name', type:'string'},
                    {name:'Who', type:'string'},
                    {name:'StartDate', type : 'date', dateFormat:'c'},
                    {name:'EndDate', type : 'date', dateFormat:'c'},
                    {name:'PercentDone'},
                    {name:'ParentId', type: 'auto'},
                    {name:'IsLeaf', type: 'bool'},

                    // Your task meta data goes here
                    {name:'Responsible'}
                ]
            })
        });
        
        var dependencyStore = new Ext.data.Store({   
            autoLoad : true,
            proxy : new Ext.data.HttpProxy({
                url : 'dependencies.xml',
                method:'GET'
            }),
            reader: new Ext.data.XmlReader({
                // records will have a 'Task' tag
                record: 'Link',
                fields : [
                    // 3 mandatory fields
                    {name:'From', type : 'int'},
                    {name:'To', type : 'int'},
                    {name:'Type', type : 'int'}
                ]
            })
        });
        
        
        var g = new Sch.TreeGanttPanel({
            height : Ext.getBody().getHeight(),
            width: '100%',
            renderTo : Ext.getBody(),
            leftLabelField : 'Name',
            highlightWeekends : false,
            showTodayLine : true,
            loadMask : true,
            enableDependencyDragDrop : false,
            
            tooltipTpl : new Ext.XTemplate(
                '<table class="taskTip">', 
                    '<tr><td>开始:</td> <td align="right">{[values.StartDate.format("y-m-d")]}</td></tr>',
                    '<tr><td>结束:</td> <td align="right">{[values.EndDate.format("y-m-d")]}</td></tr>',
                    '<tr><td>进度:</td><td align="right">{PercentDone}%</td></tr>',
                    '<tr><td>负责人:</td><td align="right">{Who}</td></tr>',
                '</table>'
            ).compile(),
            
            viewModel : {
                start : start, 
                end : end, 
                columnType : 'monthAndQuarters',
                viewBehaviour : Sch.ViewBehaviour.MonthView
            },
            
            // Setup your static columns
            colModel : new Ext.ux.grid.LockingColumnModel({
                columns : [
                   {
                        header : 'Tasks', 
                        sortable:true, 
                        dataIndex : 'Name', 
                        locked : true,
                        width:150, 
                        editor : new Ext.form.TextField()
                   }
                ]
            }),
            store : store,
            dependencyStore : dependencyStore,
            trackMouseOver : false,
            plugins : [new Sch.plugins.Pan()],
            stripeRows : true
        });
        
        return g;
    }
};

Ext.onReady(function() {
	App.Scheduler.init(); 
	Ext.select('a[href=http://www.ext-scheduler.com/store.html]').toggle();
});