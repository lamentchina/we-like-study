CREATE PROCEDURE `education_exam__init4test`()
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

按月份循环 * 8
  插入一份统考试卷
  插入一张试卷  
  插入50道题目  
    插入30道单选题    
    插入10道多选题    
    插入10道单选题    
  循环5个班级,插入统考安排信息
    插入1条 考试-班级 信息    
    按每个学生循环 * 40   
      插入1条 学生-考试 信息    
      插入1条试卷做题日志    
      插入50条做题日志
        根据月份,设置每一题答对概率    
      卷子提交        
        更新卷子日志的成绩      
        更新 学生-考试 信息        
    更新班级的统考统计成绩: 平均分,最高分,及格人数

*/

#所有的试卷,卷子,都是由教师 JS0000002,张三2,5101,第一教研组 提供上传的
declare teacher_id_creater_ int default '206';
declare teacher_code_creater_ varchar(200) default 'JS0000002';
declare teacher_name_creater_ char(5) default '张三2';
declare teacher_id_creater_group_ int default '11';
declare teacher_code_creater_group_ char(4) default '5101';

declare student_id_,student_group_id_ int default '1';
declare student_name_,student_code_,student_group_code_,student_group_name_ varchar(200) default '0';
declare status_,type_ int default '0';

#科目信息
declare subject_code_ char(2) default '00';
declare subject_name_ char(4) default '0000';

#各层循环所需要的变量: 月份,科目,试卷,题目,试卷日志,题目日志
declare count_month,count_subject,count_paper,count_question,count_class,count_student,count_paperlog,count_questionlog int default 0;
#各个业务表所需要的主键
declare paperid,examid,questionid,education_exam_2_class__id,education_exam_2_student__id,education_paper_log_id_ int default 0;

#
declare papertitle varchar(200) default '';
declare questiontitle varchar(200) default '';

declare userid,usergroupid int default 0;
declare personname varchar(200) default '0' ;
declare subjectcode varchar(200) default '';

declare paperdate char(10);
declare theanswer char(1);

declare randmonth,lograte,thelograte int default 0;
declare x1,x2,x3,x4,x5,x6,x7 int default 0;
declare x8 varchar(200) default '';

truncate table education_paper ;
truncate table education_paper_2_question ;
truncate table education_question ;
truncate table education_exam ;
truncate table education_exam_2_class ;
truncate table education_exam_2_student ;

update basic_memory set extend1 = 0 where type = 2 and code in (
'education_paper'    
,'education_paper_2_question'
,'education_question'
,'education_exam'
,'education_exam_2_class'
,'education_exam_2_student'
,'education_paper_log'
);

#启用事务功能
START TRANSACTION; 

#根据月份循环
set count_month = 8 ;
while_month:while count_month >0 do
    set count_month = count_month - 1;        
    if count_month = 7 then set paperdate = '2011-09-01'; end if;    
    if count_month = 6 then set paperdate = '2011-10-01'; end if;    
    if count_month = 5 then set paperdate = '2011-11-01'; end if;    
    if count_month = 4 then set paperdate = '2011-12-01'; end if;    
    if count_month = 3 then set paperdate = '2012-03-01'; end if;    
    if count_month = 2 then set paperdate = '2012-04-01'; end if;    
    if count_month = 1 then set paperdate = '2012-05-01'; end if;    
    if count_month = 0 then set paperdate = '2012-06-01'; end if;    

    #根据科目循环,科目编号从 50 到 53 
    set count_subject = 4;    
    while_subject: while count_subject > 0 do        
        set count_subject = count_subject - 1;        
        select code,name into subject_code_,subject_name_ from education_subject where code = concat('5',count_subject);                      
                
        set papertitle = concat('test paper title ',round(rand()*10000));        
        set paperid = basic_memory__index('education_paper');         
        set examid = basic_memory__index('education_exam');                 

        #插入一张统考卷    
        insert into education_exam (                
            id            
            ,id_paper   
                    
            ,title   
            ,time_start
            ,time_end
            ,score
            ,`mode`
            ,passline
            ,`type`       
            ,place
            ,count_students_planed
            ,count_students    
                   
            ,subject_code        
            ,subject_name   
            ,teacher_id            
            ,teacher_name            
            ,teacher_code                
            ,id_creater            
            ,id_creater_group            
            ,code_creater_group            
            ,time_created
        ) values (        
            examid            
            ,paperid         
            
            ,papertitle  
            ,paperdate                 
            ,paperdate          
            ,100            
            ,1            
            ,60            
            ,1            
            ,'net oline'            
            ,'200'            
            ,'200'
               
            ,subject_code_            
            ,subject_name_     
            ,teacher_id_creater_            
            ,teacher_name_creater_            
            ,teacher_code_creater_            
            ,teacher_id_creater_            
            ,teacher_id_creater_group_            
            ,teacher_code_creater_group_              
            ,paperdate
        );   

        #插入一张试卷
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
            ,time_created
        ) values (
             subject_code_            
            ,subject_name_              
            ,100            
            ,0            
            ,papertitle            

            ,2            
            ,teacher_id_creater_            
            ,teacher_name_creater_            
            ,teacher_code_creater_               
            ,100            

            ,1            
            ,paperid            
            ,teacher_id_creater_            
            ,teacher_id_creater_group_            
            ,teacher_code_creater_group_              
            ,1            
            ,'test data'            
            ,paperdate
        );    
                
        #先插入25道单选题 
        set questionid = basic_memory__index('education_question');       
        insert into education_question (
            type2
            
            ,title
            ,answer
            ,optionlength
            ,option1
            ,option2
            ,option3
            ,option4
            ,option5
            ,option6
            ,option7
            ,description
            ,cent
            
            ,layout
            ,id_parent
            ,path_listen
            ,path_image
            
            ,subject_code
            ,subject_name
            ,teacher_id
            ,teacher_name
            ,teacher_code
            
            ,count_used
            ,count_right
            ,count_wrong
            ,count_giveup
            
            ,comment_ywrong_1
            ,comment_ywrong_2
            ,comment_ywrong_3
            ,comment_ywrong_4
            
            ,difficulty
            ,ids_level_knowledge
            
            ,type
            ,id
            ,id_creater
            ,id_creater_group
            ,code_creater_group            

            ,status
            ,remark

        ) values (
            '0'
            
            ,'25 single choices  '  
            ,'A'
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
            
            ,'0'
            ,'0'
            ,'0'
            ,'0'
            
            ,subject_code_            
            ,subject_name_  
            ,teacher_id_creater_            
            ,teacher_name_creater_            
            ,teacher_code_creater_   
            
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
            
            ,'7'
            ,questionid
            ,teacher_id_creater_            
            ,teacher_id_creater_group_            
            ,teacher_code_creater_group_  
             
            ,1
            ,'data test'

        ); 
        insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);        

        set count_question = 25;    
        while_question:while count_question > 0 do        
            set count_question = count_question - 1;    
            set questionid = basic_memory__index('education_question');    
            set questiontitle = concat('test option ',round(rand()*100000));    
            insert into education_question (
                type2
                
                ,title
                ,answer
                ,optionlength
                ,option1
                ,option2
                ,option3
                ,option4
                ,option5
                ,option6
                ,option7
                ,description
                ,cent
                
                ,layout
                ,id_parent
                ,path_listen
                ,path_image
                
                ,subject_code
                ,subject_name
                ,teacher_id
                ,teacher_name
                ,teacher_code
                
                ,count_used
                ,count_right
                ,count_wrong
                ,count_giveup
                
                ,comment_ywrong_1
                ,comment_ywrong_2
                ,comment_ywrong_3
                ,comment_ywrong_4
                
                ,difficulty
                ,ids_level_knowledge
                
                ,type
                ,id
                ,id_creater
                ,id_creater_group
                ,code_creater_group            
    
                ,status
                ,remark
    
            ) values (
                '0'
                
                ,questiontitle
                ,'A'
                ,4
                ,concat('test option ',round(rand()*100000),' A')
                ,concat('test option ',round(rand()*100000),' B')
                ,concat('test option ',round(rand()*100000),' C')
                ,concat('test option ',round(rand()*100000),' D')
                ,'0'
                ,'0'
                ,'0'
                ,concat('description test',round(rand()*100000))
                ,'2'
                
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                
                ,subject_code_            
                ,subject_name_  
                ,teacher_id_creater_            
                ,teacher_name_creater_            
                ,teacher_code_creater_   
                
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
                
                ,'1'
                ,questionid
                ,teacher_id_creater_            
                ,teacher_id_creater_group_            
                ,teacher_code_creater_group_  
                 
                ,1
                ,'data test'
    
            );       
           insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);
        end while while_question;

        #然后是15道多选题,不过答案都是 A 
        set questionid = basic_memory__index('education_question');       
        insert into education_question (
            type2
            
            ,title
            ,answer
            ,optionlength
            ,option1
            ,option2
            ,option3
            ,option4
            ,option5
            ,option6
            ,option7
            ,description
            ,cent
            
            ,layout
            ,id_parent
            ,path_listen
            ,path_image
            
            ,subject_code
            ,subject_name
            ,teacher_id
            ,teacher_name
            ,teacher_code
            
            ,count_used
            ,count_right
            ,count_wrong
            ,count_giveup
            
            ,comment_ywrong_1
            ,comment_ywrong_2
            ,comment_ywrong_3
            ,comment_ywrong_4
            
            ,difficulty
            ,ids_level_knowledge
            
            ,type
            ,id
            ,id_creater
            ,id_creater_group
            ,code_creater_group            

            ,status
            ,remark

        ) values (
            '0'
            
            ,'15 multiple choices  '  
            ,'A'
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
            
            ,'0'
            ,'0'
            ,'0'
            ,'0'
            
            ,subject_code_            
            ,subject_name_  
            ,teacher_id_creater_            
            ,teacher_name_creater_            
            ,teacher_code_creater_   
            
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
            
            ,'7'
            ,questionid
            ,teacher_id_creater_            
            ,teacher_id_creater_group_            
            ,teacher_code_creater_group_  
             
            ,1
            ,'data test'

        ); 
        insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);
        set count_question = 15;    
        while_question2:while count_question > 0 do        
            set count_question = count_question - 1;    
            set questionid = basic_memory__index('education_question');    
            set questiontitle = concat('multiple choices ',round(rand()*100000));    
            insert into education_question (
                type2
                
                ,title
                ,answer
                ,optionlength
                ,option1
                ,option2
                ,option3
                ,option4
                ,option5
                ,option6
                ,option7
                ,description
                ,cent
                
                ,layout
                ,id_parent
                ,path_listen
                ,path_image
                
                ,subject_code
                ,subject_name
                ,teacher_id
                ,teacher_name
                ,teacher_code
                
                ,count_used
                ,count_right
                ,count_wrong
                ,count_giveup
                
                ,comment_ywrong_1
                ,comment_ywrong_2
                ,comment_ywrong_3
                ,comment_ywrong_4
                
                ,difficulty
                ,ids_level_knowledge
                
                ,type
                ,id
                ,id_creater
                ,id_creater_group
                ,code_creater_group            
    
                ,status
                ,remark
    
            ) values (
                '0'
                
                ,questiontitle
                ,'A'
                ,4
                ,concat('test option ',round(rand()*100000),' A')
                ,concat('test option ',round(rand()*100000),' B')
                ,concat('test option ',round(rand()*100000),' C')
                ,concat('test option ',round(rand()*100000),' D')
                ,'0'
                ,'0'
                ,'0'
                ,concat('description test',round(rand()*100000))
                ,'2'
                
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                
                ,subject_code_            
                ,subject_name_  
                ,teacher_id_creater_            
                ,teacher_name_creater_            
                ,teacher_code_creater_   
                
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
                
                ,'2'
                ,questionid
                ,teacher_id_creater_            
                ,teacher_id_creater_group_            
                ,teacher_code_creater_group_  
                 
                ,1
                ,'data test'
    
            );       
           insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);
        end while while_question2; 

        #10道判断题
        set questionid = basic_memory__index('education_question');       
        insert into education_question (
            type2
            
            ,title
            ,answer
            ,optionlength
            ,option1
            ,option2
            ,option3
            ,option4
            ,option5
            ,option6
            ,option7
            ,description
            ,cent
            
            ,layout
            ,id_parent
            ,path_listen
            ,path_image
            
            ,subject_code
            ,subject_name
            ,teacher_id
            ,teacher_name
            ,teacher_code
            
            ,count_used
            ,count_right
            ,count_wrong
            ,count_giveup
            
            ,comment_ywrong_1
            ,comment_ywrong_2
            ,comment_ywrong_3
            ,comment_ywrong_4
            
            ,difficulty
            ,ids_level_knowledge
            
            ,type
            ,id
            ,id_creater
            ,id_creater_group
            ,code_creater_group            

            ,status
            ,remark

        ) values (
            '0'
            
            ,'10 check  '  
            ,'A'
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
            
            ,'0'
            ,'0'
            ,'0'
            ,'0'
            
            ,subject_code_            
            ,subject_name_  
            ,teacher_id_creater_            
            ,teacher_name_creater_            
            ,teacher_code_creater_   
            
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
            
            ,'7'
            ,questionid
            ,teacher_id_creater_            
            ,teacher_id_creater_group_            
            ,teacher_code_creater_group_  
             
            ,1
            ,'data test'

        ); 
        insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);
        set count_question = 10;    
        while_question3:while count_question > 0 do        
            set count_question = count_question - 1;    
            set questionid = basic_memory__index('education_question');    
            set questiontitle = concat('check ',round(rand()*100000));    
            insert into education_question (
                type2
                
                ,title
                ,answer
                ,optionlength
                ,option1
                ,option2
                ,option3
                ,option4
                ,option5
                ,option6
                ,option7
                ,description
                ,cent
                
                ,layout
                ,id_parent
                ,path_listen
                ,path_image
                
                ,subject_code
                ,subject_name
                ,teacher_id
                ,teacher_name
                ,teacher_code
                
                ,count_used
                ,count_right
                ,count_wrong
                ,count_giveup
                
                ,comment_ywrong_1
                ,comment_ywrong_2
                ,comment_ywrong_3
                ,comment_ywrong_4
                
                ,difficulty
                ,ids_level_knowledge
                
                ,type
                ,id
                ,id_creater
                ,id_creater_group
                ,code_creater_group            
    
                ,status
                ,remark
    
            ) values (
                '0'
                
                ,questiontitle
                ,'A'
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                ,concat('description test',round(rand()*100000))
                ,'2'
                
                ,'0'
                ,'0'
                ,'0'
                ,'0'
                
                ,subject_code_            
                ,subject_name_  
                ,teacher_id_creater_            
                ,teacher_name_creater_            
                ,teacher_code_creater_   
                
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
                
                ,'3'
                ,questionid
                ,teacher_id_creater_            
                ,teacher_id_creater_group_            
                ,teacher_code_creater_group_  
                 
                ,1
                ,'data test'
    
            );       
           insert into education_paper_2_question (id_paper,id_question) values (paperid,questionid);
        end while while_question3;                                                                                  

        #每个班级都要做这个考卷   
        #班级的编号是  5 6 7 8 9  
        set count_class = 5;    
        while_class: while count_class > 0 do        
            set count_class = count_class - 1;   
            select id,code,name into @class_id,@class_code,@class_name from basic_group where id = (4+count_class) ;            
            set education_exam_2_class__id = basic_memory__index('education_exam_2_class');    
        
            #status 描述: 0 错误, 1 正确并流程已结束, 
            #2 正在走业务流程, 21 走业务流程,等待某部门审批通过, 22 等待多个部门共同审批,需要全部通过, 23 多审批,单通过
            #3 原始业务数据编辑状态, 31 新建状态,新建后保存等待下次更改,尚未发布, 32 修改状态,因某种原因,数据正在被修改中,            
            #4 正式发布                         
            #5 作废 , 51 因实文件有效期到期正常作废 , 52 因异常事件作废
            #在一般的OA中,数据的状态是:            
            # 31(用户新建) - 21(等待单点审批) - 4(审批通过) - 5(作废)            
            #或者 31-21-32-21-32-21-4-5, 表示审批驳回修改

            #type 描述: 1 统考 , 2 月考 , 3 期中考, 4 期末考        
            set type_ = 1;set status_ = 1;    
            if paperdate = '2012-05-01' then            
                set status_ = 4;                
                set type_ = 2;                
            end if;            
            if paperdate = '2012-06-01' then            
                set status_ = 31;                
                set type_ = 4;                
            end if;
            insert into education_exam_2_class (            
                 paper_id
                ,exam_id
                ,exam_title
                ,class_id
                ,class_code
                ,class_name
                ,teacher_id
                ,teacher_name
                ,teacher_code
                
                ,subject_code
                ,subject_name
                
                ,count_students_planed
                ,count_students
                
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
                 paperid
                ,examid
                ,papertitle
                ,@class_id
                ,@class_code
                ,@class_name
                ,teacher_id_creater_            
                ,teacher_name_creater_            
                ,teacher_code_creater_  
                
                ,subject_code_        
                ,subject_name_  
                
                ,'40'
                ,'40'
                
                ,type_
                ,education_exam_2_class__id
                ,teacher_id_creater_            
                ,teacher_id_creater_group_            
                ,teacher_code_creater_group_  
                ,paperdate
                ,paperdate
                ,0
                ,status_
                ,'test data '
            );
        end while while_class;        

        #每个学生都要做这张考卷, 学生编号从 5 到 204 ,总计 200 个   
        if paperdate < '2012-06-01' then                

            set count_student = 200;  
            while_student: while count_student > 0 do    
                set count_student = count_student - 1;               
                select id            
                       ,group_name
                       ,group_code
                       ,group_id
                       ,person_name
                       ,username 
                into 
                       student_id_                   
                       ,student_group_name_
                       ,student_group_code_
                       ,student_group_id_
                       ,student_name_
                       ,student_code_ 
                from basic_user where id = (count_student + 5); 
                
                set education_exam_2_student__id = basic_memory__index('education_exam_2_student');
                set education_paper_log_id_ = education_exam_2_student__id;
                if paperdate = '2012-05-01' then             
                    set education_paper_log_id_ = 0;                
                end if;
                insert into education_exam_2_student (            
                    exam_id
                    ,exam_title
                    ,class_id
                    ,class_code
                    ,class_name
                    ,teacher_id
                    ,teacher_name
                    ,teacher_code
                    ,student_id
                    ,student_name
                    ,student_code
                    
                    ,subject_code
                    ,subject_name
                    
                    ,id_paper                
                    ,id_paper_log
                    
                    ,time_start
                    ,time_end                
                    ,passline                
                    ,totalcent
                    
                    ,type
                    ,id
                    ,id_creater
                    ,id_creater_group
                    ,code_creater_group
                    ,time_created
                ) values (            
                    examid
                    ,papertitle
                    ,student_group_id_
                    ,student_group_code_
                    ,student_group_name_
                    ,teacher_id_creater_
                    ,teacher_name_creater_
                    ,teacher_code_creater_
                    ,student_id_
                    ,student_name_
                    ,student_code_
                    
                    ,subject_code_
                    ,subject_name_
                    
                    ,paperid                
                    ,education_paper_log_id_
                    
                    ,paperdate
                    ,paperdate             
                    ,'60'                
                    ,'100'
                    
                    ,'1'
                    ,education_exam_2_student__id
                    ,student_id_
                    ,student_group_id_
                    ,student_group_code_
                    ,paperdate
                );
            end while while_student;        
        end if;
    end while while_subject;
end while while_month;
commit;
END;
