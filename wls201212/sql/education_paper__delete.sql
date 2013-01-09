CREATE PROCEDURE `education_paper__delete`(IN `ids` varchar(200),out out_state int,out out_msg varchar(200))
pro_main:begin
/*
批量删除试卷数据

@author wei1224hf@gmail.com
@version 201301
*/        
    if ids is null then        
        set out_state = 0;        
        set out_msg = 'ids null';        
        leave pro_main;        
    end if;
         
    set @sql_ = concat("delete from education_paper where id in (",ids,") "); 
    PREPARE stmt FROM @sql_;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;      

    #删除题目
    set @sql_ = concat("delete from education_question where id in (select id_question from education_paper_2_question where id_paper in (",ids,")) ");
    PREPARE stmt FROM @sql_;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;     

    #删除 题目-试卷关系
    set @sql_ = concat("delete from education_paper_2_question where id_paper in (",ids,") ");
    PREPARE stmt FROM @sql_;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;     

    #删除做题日志
    set @sql_ = concat("delete from education_question_log where id_paper in (",ids,") ");
    PREPARE stmt FROM @sql_;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;     

    #删除试卷日志
    set @sql_ = concat("delete from education_paper_log where paper_id in (",ids,") ");
    PREPARE stmt FROM @sql_;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt; 

END;
