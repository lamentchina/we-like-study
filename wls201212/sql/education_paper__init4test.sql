CREATE PROCEDURE `education_paper__init4test`(in in_papercount int)
pro_main:BEGIN
/*
随机,自动得往系统中插入很多张试卷

@version 201209
@author wei1224hf@gmail.com
*/

declare _group_id,
        _question_id,
        _user_id,
        _paper_id,
        _count_questions int default 0;
declare _subject_code,
        _subject_name,
        _group_name,
        _papertitle,
        __questiontitle,
        _username,
        _personname,
        _group_code varchar(200) default '';

/*
truncate table education_paper;
truncate table education_question ;
truncate table education_paper_log ;
truncate table education_question_log ;
truncate table education_question_log_wrongs ;
*/

delete from education_paper where remark = 'education_paper__init4test';

if in_papercount is null or in_papercount = 0 then
    set in_papercount = 20;    
end if;

while_paper:while in_papercount > 0  do
    set in_papercount = in_papercount - 1;    
    
    #得到一个随机的教师     
    SELECT
        basic_user.username,
        basic_user.group_id,
        basic_user.group_code,
        basic_user.person_name,
        basic_user.id        
        into        
        _username        
        ,_group_id        
        ,_group_code        
        ,_personname        
        ,_user_id
        FROM
        basic_user
        where type = 3 order by rand() limit 1;         

    #得到一个随机的科目
    select code,name into _subject_code,_subject_name from education_subject
        where type = 2 or type = 3 order by rand() limit 1;     

    #插入一张试卷    
    set _papertitle = concat('测试数据试卷',round(rand()*10000));        
    set _paper_id = basic_memory__index('education_paper');  
    insert into education_paper (
        subject_code
        ,subject_name
        ,count_questions
        ,count_used
        ,title
        ,cost
        ,teacher_id
        ,teacher_name
        ,teacher_code
        ,cent
        ,type
        ,id
        ,id_creater
        ,id_creater_group
        ,code_creater_group
        ,status        
        ,remark
     ) values (
        _subject_code
        ,_subject_name
        ,'50'
        ,'0'
        ,_papertitle
        ,floor(rand()*5)
        ,_user_id
        ,_personname
        ,_username
        ,100
        ,1
        ,_paper_id
        ,_user_id
        ,_group_id
        ,_group_code        
        ,4        
        ,'education_paper__init4test'
    );        


    #50道单项选择题
    set _question_id = basic_memory__index('education_question');       
    insert into education_question (
        type
        ,subject
        ,title
        ,id
        ,id_creater
        ,id_creater_group            
        ,author
    ) values (
        7
        ,subjectcode        
        ,'50道单项选择题'
        ,questionid
        ,userid
        ,usergroupid            
        ,personname
    ); 
    insert into education_paper_2_question (id_paper,id_question) values (paper_id,question_id);
    set _count_questions = 50;    
    while_question:while _count_questions > 0 do        
        set _count_questions = _count_questions - 1;    
        set _questionid = basic_memory__index('education_question');    
        set _questiontitle = concat('测试数据题目',round(rand()*100000));    
        insert into education_question (
            type
            ,subject
            ,title
            ,answer
            ,optionlength
            ,option1
            ,option2
            ,option3
            ,option4
            ,description
            ,cent
            ,id
            ,id_creater
            ,id_creater_group            
            ,author
        ) values (
            1
            ,subjectcode        
            ,_questiontitle
            ,'A'
            ,4
            ,concat('测试数据选项',round(rand()*100000),' A')
            ,concat('测试数据选项',round(rand()*100000),' B')
            ,concat('测试数据选项',round(rand()*100000),' C')
            ,concat('测试数据选项',round(rand()*100000),' D')
            ,concat('测试数据解题思路',round(rand()*100000))
            ,2
            ,questionid
            ,userid
            ,usergroupid            
            ,personname
        );        
       insert into education_paper_2_question (id_paper,id_question) values (paper_id,question_id);
    end while while_question;    

    #30道多项选择题
    set _questionid = basic_memory__index('education_question');       
    insert into education_question (
        type
        ,subject
        ,title
        ,id
        ,id_creater
        ,id_creater_group            
        ,author
    ) values (
        7
        ,subjectcode        
        ,'30道多项选择题'
        ,questionid
        ,userid
        ,usergroupid            
        ,personname
    ); 
    insert into education_paper_2_question (id_paper,id_question) values (paper_id,question_id);
    set _count_questions = 30;    
    while_question2:while _count_questions > 0 do        
        set _count_questions = _count_questions - 1;    
        set _questionid = basic_memory__index('education_question');    
        set _questiontitle = concat('测试数据题目',round(rand()*100000));    
        insert into education_question (
            type
            ,subject
            ,title
            ,answer
            ,optionlength
            ,option1
            ,option2
            ,option3
            ,option4
            ,description
            ,cent
            ,id
            ,id_creater
            ,id_creater_group            
            ,author
        ) values (
            2
            ,subjectcode        
            ,_questiontitle
            ,'A,B,C,D'
            ,4
            ,concat('测试数据选项',round(rand()*100000),' A')
            ,concat('测试数据选项',round(rand()*100000),' B')
            ,concat('测试数据选项',round(rand()*100000),' C')
            ,concat('测试数据选项',round(rand()*100000),' D')
            ,concat('测试数据解题思路',round(rand()*100000))
            ,2
            ,questionid
            ,userid
            ,usergroupid            
            ,personname
        );        
       insert into education_paper_2_question (id_paper,id_question) values (paper_id,question_id);
    end while while_question2;    

    #20道单选题
    set _questionid = basic_memory__index('education_question');       
    insert into education_question (
        type
        ,subject
        ,title
        ,id
        ,id_creater
        ,id_creater_group            
        ,author
    ) values (
        7
        ,subjectcode        
        ,'20道单选题'
        ,questionid
        ,userid
        ,usergroupid            
        ,personname
    ); 
    insert into education_paper_2_question (id_paper,id_question) values (_paperid,_questionid);
    set _count_questions = 20;    
    while_question3:while _count_questions > 0 do        
        set _count_questions = _count_questions - 1;    
        set _questionid = basic_memory__index('education_question');    
        set _questiontitle = concat('测试数据题目',round(rand()*100000));    
        insert into education_question (
            type
            ,subject
            ,title
            ,answer
            ,optionlength
            ,description
            ,cent
            ,id
            ,id_creater
            ,id_creater_group            
            ,author
        ) values (
            3
            ,subjectcode        
            ,_questiontitle
            ,'A'
            ,2
            ,concat('测试数据解题思路',round(rand()*100000))
            ,2
            ,questionid
            ,userid
            ,usergroupid            
            ,personname
        );        
       insert into education_paper_2_question (id_paper,id_question) values (paper_id,question_id);
    end while while_question3;

end while while_paper;

END;
