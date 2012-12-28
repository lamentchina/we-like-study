CREATE FUNCTION `basic_memory__index`(in_tablename varchar(200)) RETURNS int(11)
BEGIN
/*
得到某一张系统业务表的主键
很多业务表中,都有 id 这个字段,是作为唯一主键存在的,并且不是 auto_increment 
所以需要依赖额外的主键生成函数

@author wei1224hf@gmail.com
@version 201212
*/

    declare id_ int;
    
    select extend1 into id_ from basic_memory where type = 2 and code = in_tablename;    
    if id_ is null then 
        set id_ = 0;
    end if;
    update basic_memory set extend1 = extend1+1, extend2 = extend2 + 1 where type = 2 and code = in_tablename;  
    RETURN id_+1;
END;
