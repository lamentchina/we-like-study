CREATE PROCEDURE `education_question_log_wrongs__submit`(
IN in_id_paper_log int
,OUT out_state int
,OUT out_msg VARCHAR(200)
)
pro_papermark:BEGIN
/*
错题本批改

@version 201209
@author wei1224hf@gmail.com
*/
declare _myanswer,_answer,_count_wrong,_count_right,_id_question,_id_creater,_id,fig
    int default 0;      

#定义游标
declare cur cursor for     
    SELECT
    education_question_log.myanswer,
    education_question.answer,
    education_question_log_wrongs.count_wrong,
    education_question_log_wrongs.count_right,
    education_question_log.id_question,      
    education_question_log.id_creater,
    education_question_log_wrongs.id
    FROM
    education_question_log
    Left Join education_question ON education_question_log.id_question = education_question.id
    Left Join education_question_log_wrongs ON education_question_log.id_question = education_question_log_wrongs.question_id
    WHERE
    education_question_log.id_paper_log =  in_id_paper_log;
          

#以下变量用于游标
declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;
SET fig=0;
#DECLARE CONTINUE HANDLER FOR 1062 SET out_msg='wrong';         

    START TRANSACTION; 
    open cur;
    fetch cur into _myanswer,_answer,_count_wrong,_count_right,_id_question,_id_creater,_id;
    WHILE ( fig = 0 ) DO            
        if _myanswer = _answer then
            if _count_right = 4 then                         
                delete from education_question_log_wrongs where id = _id;
            else
                update education_question_log_wrongs set 
                    count_right = count_right + 1 ,                                
                    time_lastupdated = now()
                    where id = _id;                
            end if;
        else    
            update education_question_log_wrongs set 
                count_wrong = count_wrong + 1 ,                                
                time_lastupdated = now()
                where id = _id;
        end if;        

    fetch cur into _myanswer,_answer,_count_wrong,_count_right,_id_question,_id_creater,_id;
    END WHILE;
    close cur;                                              

    SELECT
    education_question.answer,
    education_question.description
    FROM
    education_question_log
    Left Join education_question ON education_question_log.id_question = education_question.id
    WHERE
    education_question_log.id_paper_log =  in_id_paper_log
    order by education_question.id;    

    delete from education_question_log where id_paper_log = in_id_paper_log;
    set out_state = 1;                
    set out_msg = 'done';            
    commit;
END;
