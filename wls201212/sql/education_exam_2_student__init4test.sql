CREATE PROCEDURE `education_exam_2_student__init4test`()
pro_main:BEGIN
/*
模拟统考数据,整一年的数据
5个班级,201个学生,高三一年,8个月,每个月一次月考
2011年秋季到2012年春季,每个学生的做题情况,他们的成绩按月份逐月上升

月份 概率 浮动
9    60%  10%
10   70%  9%
11   80%  8% 
12   85%  7%
3    87%  6%
4    88%  5%
5    90%  4%
6    95%  3%

先决条件: 已经将 考卷-学生 记录数据准备好,内有 6400 条记录,
此存储过程将把其中的 6000 条记录处理掉,模拟6000次学生做题交卷过程

读取一条 学生-考卷 记录
  得到 考试编号  
       试卷编号  
       当天日期     
         通过 education_paper_2_question 得到这张试卷最大的题目编号,         
         从而可以推出这张试卷有关的所有100个题目编号         
       插入一条试卷做题日志,得到编号       
       循环100次,插入做题日志
*/

#科目信息
declare subject_code_ char(2) default '00';
declare subject_name_ char(4) default '0000';

#学生信息
declare student_id_,student_group_id_ int default '0';
declare student_name_,student_code_,student_group_code_,student_group_name_ varchar(200) default '0';

declare count_exam2student,count_question int default '0';
declare paper_id_,exam_id_,paper_log_id_,question_id_,question_id_max_,question_log_id_ int default '0';
declare thedate_ char(10) default '2000-01-01';
declare lograte,thelograte int default '0';
declare theanswer char(1) default 'A';

declare id_creater_group_ int default '0';
declare code_creater_group_ varchar(200) default '0';

#学生卷子编号
declare education_exam_2_student__id int default '0';

#declare 结束,以下是正式的业务代码----------------------------------------------

select max(id) into education_exam_2_student__id from education_exam_2_student;

truncate table education_paper_log ;
truncate table education_question_log ;

update basic_memory set extend1 = 0 where type = 2 and code in (
 'education_paper_log'
, 'education_question_log'    
);

#启用事务功能
START TRANSACTION; 

set count_exam2student = 20;
while_exam2student : while count_exam2student >0 do
    set count_exam2student = count_exam2student - 1;      
    set education_exam_2_student__id = education_exam_2_student__id - 1;     
    set paper_log_id_ = basic_memory__index('education_paper_log');   
    update education_exam_2_student set 
        status = 23
        ,id_paper_log = paper_log_id_
        where id = education_exam_2_student__id;   
    
    select 
        exam_id        
        ,student_id
        ,student_name
        ,student_code
        ,subject_code
        ,subject_name    
        ,id_creater_group
        ,code_creater_group    
        ,id_paper           
        ,time_created
        
        ,exam_title   
        ,teacher_id
        ,teacher_name
        ,teacher_code
    into 
        exam_id_
        ,student_id_
        ,student_name_
        ,student_code_
        ,subject_code_
        ,subject_name_   
        ,id_creater_group_
        ,code_creater_group_         
        ,paper_id_     
        ,thedate_   

        ,@exam_title        
        ,@teacher_id
        ,@teacher_name
        ,@teacher_code
    from education_exam_2_student where id = education_exam_2_student__id;   
         
    insert into education_paper_log (    
        paper_id
        ,paper_title
        ,teacher_id
        ,teacher_name
        ,teacher_code        
        ,student_id  
        ,student_name 
        ,student_code
        
        ,cent
        ,cent_subjective
        ,cent_objective
        ,mycent
        ,mycent_subjective
        ,mycent_objective
        
        ,count_right
        ,count_wrong
        ,count_giveup
        ,count_total
        ,count_subjective
        ,count_objective
        
        ,proportion
        
        ,subject_code
        ,subject_name
        
        ,type
        ,id
        ,id_creater
        ,id_creater_group
        ,code_creater_group
        ,time_created
        ,time_lastupdated
        ,count_updated
        ,status
        ,remark
    ) values (    
        paper_id_
        ,@exam_title
        ,@teacher_id
        ,@teacher_name
        ,@teacher_code        
        ,student_id_
        ,student_name_
        ,student_code_
        
        ,'100'
        ,'0'
        ,'100'
        ,'0'
        ,'0'
        ,'0'
        
        ,'0'
        ,'0'
        ,'0'
        ,'0'
        ,'0'
        ,'0'
        
        ,'0'
        
        ,subject_code_
        ,subject_name_
        
        ,'1'
        ,paper_log_id_
        ,student_id_
        ,id_creater_group_
        ,code_creater_group_
        ,thedate_
        ,thedate_
        ,'0'
        ,'0'
        ,'0'
    );    

    if thedate_ = '2011-09-01' then        
        set lograte = 60 + floor(rand()*10) - floor(rand()*10);
    end if;    
    if thedate_ = '2011-10-01' then                
        set lograte = 70 + floor(rand()*9) - floor(rand()*9);
    end if;    
    if thedate_ = '2011-11-01' then               
        set lograte = 80 + floor(rand()*8) - floor(rand()*8);
    end if;    
    if thedate_ = '2011-12-01' then             
        set lograte = 85 + floor(rand()*7) - floor(rand()*7);
    end if;    
    if thedate_ = '2012-03-01' then           
        set lograte = 87 + floor(rand()*6) - floor(rand()*6);
    end if;    
    if thedate_ = '2012-04-01' then             
        set lograte = 89 + floor(rand()*5) - floor(rand()*5);
    end if;    
    if thedate_ = '2012-05-01' then              
        set lograte = 91 + floor(rand()*4) - floor(rand()*4);
    end if;    
    if thedate_ = '2012-06-01' then              
        set lograte = 95 + floor(rand()*3) - floor(rand()*3);
    end if; 
    
    select max(id_question) into question_id_max_ from education_paper_2_question where id_paper = paper_id_;        
    set count_question = 53;    
    while_question: while count_question > 0 do    
        set count_question = count_question - 1;          
        set question_id_ = question_id_max_ - count_question;                
        set question_log_id_ = basic_memory__index('education_question_log');          
        set thelograte = floor(rand()*100);   
        set theanswer = 'A';     
        if thelograte > lograte then                
            set theanswer = 'B';
        end if;    

        insert into education_question_log (        
            id_paper
            ,id_paper_log
            ,id_question
            
            ,myanswer
            ,correct
            ,mycent
            ,application            
            
            ,id_teacher
            
            ,type
            ,id
            ,id_creater
            ,id_creater_group
            ,code_creater_group
            ,time_created
            ,time_lastupdated
            ,count_updated
            ,status
            ,remark
        ) values (        
            paper_id_
            ,paper_log_id_
            ,question_id_
            
            ,theanswer
            ,'0'
            ,'0'
            ,'0'            
            
            ,@teacher_id
            
            ,'0'
            ,question_log_id_
            ,student_id_
            ,id_creater_group_
            ,code_creater_group_
            ,thedate_
            ,thedate_
            ,'0'
            ,'0'
            ,'0'
        );              
        
    end while while_question;          

    if mod(count_exam2student,500) = 0 then    
        commit;          
        START TRANSACTION; 
    end if;
end while while_exam2student;
commit;
END;
