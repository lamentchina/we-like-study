CREATE PROCEDURE `education_exam__mark`(IN in_idexam int,OUT out_state int,OUT out_msg varchar(200))
pro_main:BEGIN
/**
试卷批改
教师操作

启用事务,以便回滚
修改试卷状态 status 4 ,表示考试结束
遍历 paper_log , 将 logid 存在一张临时表中,因为将对 paper_log 表更新操作
  遍历 question_log , question 表,  
    判断 question_log 的做题结果是否正确,并累加 做对数 做错数 放弃数  
  更新 paper_log 表

@param id_exam 试卷编号
@param out_state 存储过程执行结果,1为正确通过,其他都表示错误.系统处理错误,2 3 4 为业务数据错误
@param out_msg 存储过程执行后返回的执行描述结果

@version 201212
@author wei1224hf@gmail.com
@qqgroup 135426431 
*/
declare id_paper_log_ int;
declare fig int;
declare cur cursor for 
    select id_paper_log from education_exam_2_student where exam_id = in_idexam;       
#以下变量用于游标
declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;      
 
    SET fig=0;    
    open cur;    
    fetch cur into id_paper_log_;    
    while( fig = 0 ) do        
        call education_paper__submit(id_paper_log_,
             @out_totalCent ,
             @out_myTotalCent ,
             @out_count_right ,
             @out_count_wrong ,
             @out_count_giveup ,
             @out_count_byTeacher ,
             @out_state ,
             @out_msg );
    fetch cur into id_paper_log_;     
    end while;
    close cur;       

END;
