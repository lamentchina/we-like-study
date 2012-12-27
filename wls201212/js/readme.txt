JS类内部函数命名规定

/*
一个.JS文件,要尽量只含有一个JS类
*/

/*
JS类名称,要尽量跟数据库中,数据库表的表名相一致
因为数据库表是标识系统业务的主要依据
这样命名,便于说明JS类的作用
*/

/*
JS类内部的函数命名

增 insert()
以及 afterInsert() 可能执行了简单的增加后,还要额外的添加一些其他内容
删 delet() 必定是结合列表功能的
改 update()一些重要的字段,将直接引用 insert() 函数内已写的
以及 afterUpdate
查看 view()

查(列表) grid()
查询条件框 search()将需要 searchOptions 这个类内部变量
批量导入 import()
批量导出 export() 将需要 searchOptions 这个类内部变量. 系统不会一次性导出全部数据,最多一次性导出1000条,视查询条件而定

读取配置文件 loadConfig() 需要将语言包,部分配置参数从服务端读取,因此需要一个内部JS变量 config

所以,一个JS类大概是这样的:

var myClass = {
    version: ''
    
    ,ajaxState: false
    
    ,config: null
    ,loadConfig: function(){}
    
    ,insert: function(){}
    ,afterInsert: function(){}
    
    ,delet: function(){}
    
    ,update: function(){}
    ,afterUpdate: function(){}
    
    ,viewPermissions: []
    ,view: function(){}
    
    ,gridPermissions: []
    ,gridColmuns: []
    ,grid: function(){}
    ,search: function(){}
    ,searchOptions: {}
    
    ,import: function(){}
    ,export: function(){}
}

*/