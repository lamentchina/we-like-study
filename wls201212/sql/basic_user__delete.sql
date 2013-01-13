CREATE PROCEDURE `basic_user__delete`(
in in_user_ids varchar(80)
out out_state int,
out out_msg varchar(200)
)
pro_main:BEGIN
/*
删除多个用户
涉及多个用户组的操作

@author wei1224hf@gmail.com
@version 201212
*/
declare _person_id,
        _user_id,
        _student_id,
        _teacher_id int ;

    set id_person_ = 0;
    select person_id into id_person_ from basic_user  where id = id_user;
    delete from basic_person where id = id_person_;
    delete from basic_user where id = id_user;

END;
