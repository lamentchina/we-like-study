CREATE PROCEDURE `basic_user__delete`(id_user int)
BEGIN
/*
删除多个用户
涉及多个用户组的操作

@author wei1224hf@gmail.com
@version 201212
*/
    declare id_person_ int ;
    set id_person_ = 0;
    select id_person into id_person_ from basic_user  where id = id_user;
    delete from basic_person where id = id_person_;
    delete from basic_user where id = id_user;

END;
