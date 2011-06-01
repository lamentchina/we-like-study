/**
 * English translations for the Scheduler component
 *
 * NOTE: To change locale for month/day names you have to use the Ext JS language pack.
 */

if (Sch.plugins && Sch.plugins.SummaryColumn) {
    Ext.override(Sch.plugins.SummaryColumn, {
        dayText : 'd',
        hourText : 'h',
        minuteText : 'min'
    });
}

if (Sch.plugins.CurrentTimeLine) {
    Sch.plugins.CurrentTimeLine.prototype.tooltipText = '当前时间';
}
if (Sch.gantt.plugins.TaskContextMenu) {	
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.add = '添加';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addMilestone = '里程碑';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addPredecessor = 'addPredecessor';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addSubtask = '下级子事项';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addSuccessor = 'addSuccessor';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addTaskAbove = '上一个事项';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.addTaskBelow = '下一个事项';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.deleteDependency = '删除依赖关系';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.deleteTask = '删除此事项';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.editLeftLabel = '编辑左边标签';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.editRightLabel = '编辑右边标签';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.newMilestoneText = '新建里程碑';
	Sch.gantt.plugins.TaskContextMenu.prototype.texts.newTaskText = '新建一个事项';
}

if (Sch.BasicViewPresets) {
    var bvp = Sch.BasicViewPresets;

    if (bvp.hourAndDay) {
        bvp.hourAndDay.displayDateFormat = 'g:i A';
        bvp.hourAndDay.headerConfig.middle.dateFormat = 'g A';
        bvp.hourAndDay.headerConfig.top.dateFormat = 'm/d/Y';
    } 
    
    if (bvp.dayAndWeek) {
        bvp.dayAndWeek.displayDateFormat = 'm/d h:i A';
        bvp.dayAndWeek.headerConfig.middle.dateFormat = 'm/d/Y';
        bvp.dayAndWeek.headerConfig.top.renderer = function(start, end, cfg) {
            var w = start.getWeekOfYear();
            return 'w.' + ((w < 10) ? '0' : '') + w + ' ' + Sch.util.Date.getShortMonthName(start.getMonth()) + ' ' + start.getFullYear();
        };
    } 

    if (bvp.weekAndDay) {
        bvp.weekAndDay.displayDateFormat = 'm/d';
        bvp.weekAndDay.headerConfig.bottom.dateFormat = 'd M';
        bvp.weekAndDay.headerConfig.middle.dateFormat = 'Y F d';
    }
    
    /*added by wonder wei*/
    if (bvp.weekAndDayLetter) {
        bvp.weekAndDayLetter.displayDateFormat = 'm/d';
        bvp.weekAndDayLetter.headerConfig.bottom.dateFormat = 'd M';
        bvp.weekAndDayLetter.headerConfig.middle.dateFormat = 'Y F d';
    }
    
    if (bvp.weekDateAndMonth) {
        bvp.weekDateAndMonth.displayDateFormat = 'm/d';
        bvp.weekDateAndMonth.headerConfig.bottom.dateFormat = 'd M';
        bvp.weekDateAndMonth.headerConfig.middle.dateFormat = 'Y F d';
    }
    
    /*end add*/
    

    if (bvp.weekAndMonth) {
        bvp.weekAndMonth.displayDateFormat = 'Y年m月d日';
        bvp.weekAndMonth.headerConfig.middle.dateFormat = 'm月d日';
        bvp.weekAndMonth.headerConfig.top.dateFormat = 'Y年m月d日';
    } 

    if (bvp.monthAndYear) {
        bvp.monthAndYear.displayDateFormat = 'Y年m月d日';
        bvp.monthAndYear.headerConfig.middle.dateFormat = 'Y年m月';
        bvp.monthAndYear.headerConfig.top.dateFormat = 'Y年';
    } 

    if (bvp.year) {
        bvp.year.displayDateFormat = 'm/d/Y';
        bvp.year.headerConfig.bottom.renderer = function(start, end, cfg) {
            return String.format('Q{0}', Math.floor(start.getMonth() / 3) + 1);
        };
        bvp.year.headerConfig.middle.dateFormat = 'Y';
    } 
}

/*chinese patch for longboo.com*/
Sch.PresetManager.registerPreset("weekAndDayLetter", {
        timeColumnWidth : 20,   // Time column width, only applicable when locked columns are used
        displayDateFormat : "Y-m-d",  // Controls how dates will be displayed in tooltips etc
        shiftIncrement : 1,     // Controls how much time to skip when calling shiftNext and shiftPrevious.
        shiftUnit : "w",      // Valid values are "MILLI", "SECOND", "MINUTE", "HOUR", "DAY", "WEEK", "MONTH", "QUARTER", "YEAR".
        defaultSpan : 12,       // By default, if no end date is supplied to a view it will show 12 hours
        timeResolution : {      // Dates will be snapped to this resolution
            unit : "d",    // Valid values are "MILLI", "SECOND", "MINUTE", "HOUR", "DAY", "WEEK", "MONTH", "QUARTER", "YEAR".
            increment : 1
        },
        headerConfig : {    // This defines your header, you must include a "middle" object, and top/bottom are optional. For each row you can define "unit", "increment", "dateFormat", "renderer", "align", and "scope"
            middle : {             
                unit : "w",
                dateFormat : "Y年Md日"
            },
            /*top : {
                unit : "Month",
                dateFormat : 'D d/m'
            },*/
            bottom:{
                increment:1,
                unit:"d",
                /*renderer:function(a, b, c, d){
                    return a.getDate();
                },*/
                dateFormat: "D"
            }
        }
});