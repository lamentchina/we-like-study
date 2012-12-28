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


20121215
这一次更新变动的有点大:
A
规定前端向后台通信的时候,都要传递的参数有:
 username           记录用户名
 session            验证使用
 user_id            当前登录用户的编号
 user_group_code    当前登录用户的用户组编码
 user_group_id      当前登录用户的用户组编号
 user_type          当前登录用户的用户类型
 action_time        执行这个操作的客户端时间(与服务端时间会不一致)
 action             执行这个操作的名称
 action_code        执行的这个操作的编码
 
需要变动的代码有:
 1 每一个前端JS文件代码,任何一个 AJAX 函数
 2 每一PHP文件代码,任何一个函数
 3 存储过程中的大量代码
 
关于 查看 功能,是否需要提供额外的权限按钮?权限按钮是否应该都放置到 grid 的toolbar上?
查看功能,其实也可以这样,仅仅是将业务表中的所有列,所有数据都提出来再显示到网页上即可
然后,会有一个统一的 fieldset ,显示以下常见的业务字段:
id,id_creater,id_creater_group,code_creater_group,type,status,
time_created,time_lastupdated,count_updated
这样操作起来更方便点