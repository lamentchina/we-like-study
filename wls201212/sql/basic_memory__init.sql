CREATE PROCEDURE `basic_memory__init`()
BEGIN
/*
初始化系统内存表.
内存表中主要存放一些会被存储过程频繁读取调用的数据,
比如系统运行参数,各个模块的下拉列表中的业务数据,语言包,各个业务表的主键

@version 201209
@author wei1224hf@gmail.com
*/  
#业务表索引        
declare id_ int;         
  
    #初始化业务表下拉列表参数
    delete from basic_memory where extend5 like '%\_%\_%\__%' ;
    insert into basic_memory (code,type,extend4,extend5)
        select code,1,value,reference from basic_parameter where reference like '%\_%\_%\__%' ;

    #初始化业务表索引    
    delete from basic_memory where code = 'basic_person' and type = 2 ;
    select max(id) from basic_person into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_person',id_,0,'2');        

    delete from basic_memory where code = 'basic_user' and type = '2' ;
    select max(id) from basic_user into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_user',id_,0,'2'); 
    
    delete from basic_memory where code = 'basic_group' and type = '2' ;
    select max(id) from basic_group into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_group',id_,0,'2');         

    delete from basic_memory where code = 'basic_department' and type = '2' ;
    select max(id) from basic_department into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_department',id_,0,'2');     
    
    delete from basic_memory where code = 'basic_permission' and type = '2' ;
    select max(id) from basic_permission into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_permission',id_,0,'2');      
    
    delete from basic_memory where code = 'basic_workflow' and type = '2' ;
    select max(id) from basic_workflow into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_workflow',id_,0,'2');    
    
    delete from basic_memory where code = 'basic_log' and type = '2' ;
    select max(id) from basic_log into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_log',id_,0,'2');          

    delete from basic_memory where code = 'basic_department' and type = '2' ;
    select max(id) from basic_department into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values ('basic_department',id_,0,'2');   
    
    /****************************************************************************************/
    delete from basic_memory where code = 'education_subject' and type = '2' ;
    select max(id) from education_subject into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_subject',id_,0,'2');   


    delete from basic_memory where code = 'education_paper' and type = '2' ;
    select max(id) from education_paper into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_paper',id_,0,'2');         

    delete from basic_memory where code = 'education_paper_log' and type = '2' ;
    select max(id) from education_paper_log into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_paper_log',id_,0,'2');  

    delete from basic_memory where code like 'education_exam' and type = '2' ;
    select max(id) from education_exam into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_exam',id_,0,'2');  

    delete from basic_memory where code like 'education_exam_2_class' and type = '2' ;
    select max(id) from education_exam_2_class into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_exam_2_class',id_,0,'2');  

    delete from basic_memory where code like 'education_exam_2_student' and type = '2' ;
    select max(id) from education_exam_2_student into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_exam_2_student',id_,0,'2');  

    delete from basic_memory where code like 'education_exam_unified' and type = '2' ;
    select max(id) from education_exam_unified into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_exam_unified',id_,0,'2');  

    delete from basic_memory where code like 'education_question' and type = '2' ;
    select max(id) from education_question into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_question',id_,0,'2');    
    
    delete from basic_memory where code = 'education_question_log' and type = '2' ;
    select max(id) from education_question_log into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_question_log',id_,0,'2');     
    
    delete from basic_memory where code = 'education_question_log_wrongs' and type = '2' ;
    select max(id) from education_question_log_wrongs into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_question_log_wrongs',id_,0,'2');                   

    delete from basic_memory where code like 'education_student' and type = '2' ;
    select max(id) from education_student into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_student',id_,0,'2');        

    delete from basic_memory where code like 'education_teacher' and type = '2' ;
    select max(id) from education_teacher into id_;    
    if id_ is null then     
        set id_ = 0;        
    end if;
    insert into basic_memory (code,extend1,extend2,type) values (  'education_teacher',id_,0,'2');    

END;