传统MVC系统中,大量的业务逻辑操作,都在服务端代码中实现
但在这个系统中,大量的业务逻辑代码,都在数据库层实现,用存储过程或函数的方式
之所以用这种方式,是以为传统的 MVC 模式有一个诟病:开发人员逐渐远离SQL代码,直到忽视 IO 操作,
一旦当系统上线后,业务数据暴增之后,问题就出现了并且不可解决

所以本系统中,业务逻辑主要被放置在SQL中,用存储过程或函数的方式实现
规定:
每一张业务表,都要有的字段有:
id
type 
status
time_created
time_lastupdated
count_updated
id_creater
id_creater_group
code_creater_group

每创建一张业务表,都要创建对应的函数或者存储过程:
__import ,用于实施 EXCEL 导入
__export ,用于实施 EXCEL 导出
__init4test , 用于批量插入上百万的数据
__immunity , 用于自检当天产生的业务数据,以定时任务的形式周期执行
__backup_month , 将本月产生的业务数据,备份到一个额外的服务器硬件上 