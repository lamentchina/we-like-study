CREATE PROCEDURE `basic_user__insert`(
in in_username varchar(200),
in in_password varchar(200),
in in_type int,
in in_cellphone char(11),
in in_email varchar(200),
out out_state int,
out out_msg varchar(200)
)
pro_main:BEGIN
/*
对于 用户 表,核心业务属性字段就只有 用户名,密码,用户组,类型,状态 这几个
而 用户组,状态 这两个字段,是由系统或管理员设置的
所以,在新注册一个用户的时候,只要提供 用户名 密码 类型 即可
在 注册 过程中,无需提供 个人档案 跟 业务身份信息 

@author wei1224hf@gmail.com
@version 201301
*/	

declare _user_id,
        _person_id,
        _teacher_id,
        _student_id int;

   #判断必要项是否为空    
    if((in_username is NULL) ||    
       (in_password is NULL) ||       
       (in_type is NULL) ||       
       (in_cellphone is NULL) ||       
       (in_email is NULL)) then
        set out_state = 0;
        set out_msg = 'NULL input';           
        leave pro_main;
    end if;
    #判断用户名 手机号 邮箱 是否已存在        
    select id into _user_id from basic_user where (username = in_username) or 
                                 (person_cellphone = in_cellphone) or                                 
                                 (person_email = in_email);                                 
    if _user_id is not NULL then    
        set out_state = 2;        
        set out_msg = 'username or cellphone or email , already exist';        
        leave pro_main;        
    end if;    

    start transaction;        
    set _person_id = basic_memory__index('basic_person');        
    insert into basic_person (        
        id            
        ,cellphone            
        ,email
    ) values (        
        _person_id            
        ,in_cellphone            
        ,in_email
    );        

    set _user_id = basic_memory__index('basic_user');        
    insert into basic_user (
        id            
        ,username            
        ,password    
        ,person_cellphone
        ,person_email        
        ,status            
        ,type
    ) values (
        _user_id            
        ,in_username            
        ,MD5(in_password)             
        ,in_cellphone        
        ,in_email
        ,4       
        ,1
    );
    commit;     

    set out_state = 1;    
    set out_msg = concat(_user_id,';',_person_id);
END;
