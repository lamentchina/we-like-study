CREATE PROCEDURE `education_paper_log__int4test`(in count_paperlog int,out out_state int, out out_msg varchar(200))
pro_main:BEGIN
/*
为了便于模拟测试做题日志,错题本 等效果
在此,随机插入N条做题日志

@version 201210
@author wei1224hf@gmail.com
*/
declare count_papers,count_questions int default 0;
declare papertitle varchar(200) default '';
declare questiontitle varchar(200) default '';
declare paperid,questionid,papaerlogid,questionlogid int default 0;
declare userid,usergroupid int default 0;
declare personname,_username varchar(200) default '0' ;
declare subjectcode,_group_code varchar(200) default '';

declare count_month,count_subject,count_paper int default 0;
declare paperdate char(10);
declare theanswer char(1);

declare randmonth,lograte,thelograte int default 0;
declare plogmax,plogmin int default 0;
declare _title,
        _subject_name,        
        _subject_code,        
        _teacher_name,
        _teacher_code varchar(200);   

declare _teacher_id int;
truncate table education_paper_log ;
truncate table education_question_log ;
truncate table education_question_log_wrongs ;

update basic_memory set extend1 = 0 where type = 2 and code in (
 'education_paper_log'
,'education_question_log'
,'education_question_log_wrongs'
);

START TRANSACTION; 
##再插入做题日志
#至少需要 38000 条测试数据才可以

    if count_paperlog is null then
        set count_paperlog = 2;    
    end if;    

    while_log:while count_paperlog > 0 do
        set count_paperlog = count_paperlog - 1;    
           
        ##随机的一个学生 
        select 
             basic_user.id
            ,basic_user.group_id         
            ,basic_user.person_name            
            ,basic_user.group_code            
            ,basic_user.username
        into 
             userid
            ,usergroupid         
            ,personname            
            ,_group_code            
            ,_username
        from basic_user 
            where type = 2 and id < 10 order by rand() limit 1;          
            
        set randmonth = floor(rand()*8) ;    
        if randmonth = 7 then 
            set paperdate = '2011-09-01';         
            set lograte = 60 + floor(rand()*10) - floor(rand()*10);
        end if;    
        if randmonth = 6 then 
            set paperdate = '2011-10-01';         
            set lograte = 70 + floor(rand()*9) - floor(rand()*9);
        end if;    
        if randmonth = 5 then 
            set paperdate = '2011-11-01';         
            set lograte = 80 + floor(rand()*8) - floor(rand()*8);
        end if;    
        if randmonth = 4 then 
            set paperdate = '2011-12-01';         
            set lograte = 85 + floor(rand()*7) - floor(rand()*7);
        end if;    
        if randmonth = 3 then 
            set paperdate = '2012-03-01';         
            set lograte = 87 + floor(rand()*6) - floor(rand()*6);
        end if;    
        if randmonth = 2 then 
            set paperdate = '2012-04-01';         
            set lograte = 89 + floor(rand()*5) - floor(rand()*5);
        end if;    
        if randmonth = 1 then 
            set paperdate = '2012-05-01';         
            set lograte = 91 + floor(rand()*4) - floor(rand()*4);
        end if;    
        if randmonth = 0 then 
            set paperdate = '2012-06-01';         
            set lograte = 95 + floor(rand()*3) - floor(rand()*3);
        end if;      
    
        #select randmonth,lograte;
        #随机的一张试卷,因为所有学生都有权限访问所有科目
        select 
             education_paper.id         
             ,(select max(education_paper_2_question.id_question) from education_paper_2_question where education_paper_2_question.id_paper = education_paper.id )             
            ,title
            ,subject_name
            ,subject_code    
            ,teacher_id
            ,teacher_name
            ,teacher_code        

        into 
             paperid         
             ,questionid             
            ,_title
            ,_subject_name
            ,_subject_code       
            ,_teacher_id
            ,_teacher_name
            ,_teacher_code              

        from education_paper where type = 1 order by rand() limit 1;   
        set papaerlogid = basic_memory__index('education_paper_log');    
        
        insert into education_paper_log (
             paper_id
            ,paper_title
            ,teacher_id
            ,teacher_name
            ,teacher_code            
            ,student_id
            ,student_name
            ,student_code
            ,subject_code
            ,subject_name
            ,type
            ,id
            ,id_creater
            ,id_creater_group
            ,code_creater_group
            ,status            
            ,remark
        ) values (
             paperid
            ,_title         
            ,_teacher_id
            ,_teacher_name
            ,_teacher_code     
            ,userid
            ,personname
            ,_username
            ,_subject_name
            ,_subject_code        
            ,'0'        
            ,papaerlogid        
            ,userid    
            ,usergroupid       
            ,_group_code    
            ,'1'            
            ,'education_paper_log__init4test'
        );        
    
        set count_questions = 103;
        while_questionlog:while count_questions > 0 do    
            set count_questions = count_questions - 1;        
            #select 'S';
            set questionlogid = basic_memory__index('education_question_log'); 
            set thelograte = floor(rand()*100);   
            set theanswer = 'A';     
            if thelograte > lograte then                
                set theanswer = 'B';
            end if;        
     
            insert into education_question_log (        
                 id                    
    
                ,id_creater       
                ,id_creater_group       
                ,time_created        
                ,id_paper            
                ,id_paper_log            
                ,id_question            
                ,myanswer      
                ,code_creater_group    
                ,remark
            ) values (        
                 questionlogid     
                 
                ,userid                 
                ,usergroupid            
                ,paperdate
                ,paperid            
                ,papaerlogid            
                ,questionid            
                ,theanswer       
                ,_group_code                           
                ,'education_paper_log__init4test'
            );
    
            set questionid = questionid - 1;
        end while while_questionlog;        
        commit;        
        call education_paper__mark(papaerlogid,@x1,@x2,@x3,@x4,@x5,@x6,@x7,@x8);
        START TRANSACTION;   
    
    end while while_log;
    commit;
    set out_state = 1;set out_msg = 'OK';

END;
