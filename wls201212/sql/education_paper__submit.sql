CREATE PROCEDURE `education_paper__submit`(IN in_id_paper_log int,OUT out_totalCent int,OUT out_myTotalCent int,OUT out_count_right int,OUT out_count_wrong int,OUT out_count_giveup int,OUT out_count_byTeacher int,OUT out_state int,OUT out_msg VARCHAR(200))
pro_main:BEGIN
    declare fig int ;
    declare id_parent_ int;    
    declare id_paper_ int;             # 试卷编号  
    declare id_creater_,id_creater_group_ int default 0;   
    declare id_ int default 0;   
    declare id_question_ int default 0;     

    declare answer_ varchar(200);   
    declare myanswer_ varchar(200); 
    declare correct_ int default 0;                # 用于游标,标注这道题目是否作对了


    declare type_ int  default 0;                     # 用于游标,标注这道题目的类型 单选 多选 填空 判断
    declare cent_ int  default 0;    
    declare mycent_ int  default 0;                  # 用于游标,标注 这道题学生的得分
    declare isByTeacher int default 0;             # 用于游标,标注 这道题是否需要教师批改  
    declare wrongCounts int default 0;         
    declare wrongPaperTitle varchar(200) default '0';
    declare wrongTeacherName varchar(200) default '0';

    declare sqls text;    
    declare sql_ text;   

    #定义游标
    declare cur cursor for     
        select        
        education_question.id,        
        education_question.answer,        
        education_question.cent,
        education_question.type,
        education_question.id_parent,        
        education_question_log.myanswer
        from education_question_log left join education_question 
            on education_question_log.id_question = education_question.id             
            where education_question_log.id_paper_log = in_id_paper_log;            

    #以下变量用于游标
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;
    SET fig=0;
    #DECLARE CONTINUE HANDLER FOR 1062 SET out_msg='wrong';         

    #初始化大多数变量    
    set correct_ = 0;    
    set mycent_ = 0;     
    set out_totalCent = 0;    
    set out_myTotalCent = 0;    
    set out_count_right = 0;    
    set out_count_wrong = 0;    
    set out_count_giveup = 0;     
    set out_count_byTeacher = 0;  
    set sqls = '';    
    set sql_ = '';    

    #检查这个日志是否存在,如果不存在就退出    
    select paper_id,id_creater,id_creater_group into id_paper_,id_creater_,id_creater_group_ from education_paper_log where id = in_id_paper_log ;        

    if id_paper_ is null then         
        set out_msg = ' unexisted paperlog  ';        
        set out_state = 0;
    else
        #检查这个日志对应的试卷是否存在,如果不存在,直接报错退出    
        select id into id_paper_ from education_paper where id = id_paper_;        
        if id_paper_ is NULL then               
            set out_msg = ' wrong paperlog , no paper associated ';  
            set out_state = 0;      
        else  
            open cur;
            fetch cur into id_question_,answer_,cent_,type_,id_parent_,myanswer_;      
            WHILE ( fig = 0 ) DO            
                set correct_ = 0;
                set out_totalCent = out_totalCent + cent_ ;    #累计试卷的总分    
                set mycent_ = 0;          
                if ( type_ = 1 or type_ = 2 or type_ = 3)  then #题目为 自动批改 模式                                

                    if myanswer_ = 'I_DONT_KNOW' then                 #直接放弃不做了
                        set correct_ = 0;                                    
                        set out_count_giveup = out_count_giveup + 1;          #累计学生的放弃题目总数 
                    elseif myanswer_ = answer_ then                           #做对了                    
                        set correct_ = 1;   
                        set mycent_ = cent_;
                        set out_count_right = out_count_right + 1;            #累计学生的作对题目总数
                        set out_myTotalCent = out_myTotalCent + mycent_ ;     #累计学生的得分
                    else    
                                                                      #做错了,操作错题本
                        set correct_ = 2;                            

                        set out_count_wrong = out_count_wrong + 1;                            
                        #select id_parent_ , type_;                        
                        if ( ( id_parent_ = 0 ) && ( type_ = 1 or type_ = 2 or type_ = 3) ) then                   
                            #select id_question_,id_user_;
                            select count(id) into wrongCounts from education_question_log_wrongs where question_id = id_question_ and id_creater = id_creater_;                                                                                                
                            if wrongCounts = 0 then #错题记录不存在,就需要新插入一条                          
                                insert into education_question_log_wrongs 
                                (
                                       question_id
                                       ,id_creater                                       
                                       ,id_creater_group
                                       ,id
                                       ,question_title                                       
                                       ,paper_title                                       
                                       ,subject_code
                                ) 
                                    values
                                (
                                    id_question_
                                    ,id_creater_                                    
                                    ,id_creater_group_
                                    ,basic_memory__index('education_question_log_wrongs')                                    
                                    ,( select title from education_question where id = id_question_ )
                                    ,( select title from education_paper where id = ( select paper_id from education_paper_log where id = in_id_paper_log ) )                                    
                                    ,( select subject_code from education_question where id = id_question_ )
                                );                                                                   
                            else #错题记录存在,就需要累加错题次数        
                                update education_question_log_wrongs set 
                                    count_wrong = count_wrong + 1 ,                                
                                    time_lastupdated = now()
                                    where question_id = id_question_ and id_creater = id_creater_;                                                                
                            end if;                                                    
                        end if;                        

                    end if;        
                        
                elseif ( ( type_ = 4 and cent_ <> 0 ) or type_ = 6 ) then #题目需要教师批改        
                    set out_count_byTeacher = out_count_byTeacher + 1;                  
                else                 
                    # type = 5 or type = 7 ,组合题或大题,不用处理
                    set out_msg = ' asdf ';            
                end if;   

/*
#这几行会导致速度极慢 TODO
                update education_question_log set
                    correct = correct_,
                    mycent = mycent_ 
                        where 
                            id_question = id_question_ ;
              */                 
                                           

            fetch cur into id_question_,answer_,cent_,type_,id_parent_,myanswer_ ;      
            END WHILE;
            close cur;                           

            update education_paper_log set 
                count_right = out_count_right,
                count_wrong = out_count_wrong,
                count_giveup = out_count_giveup,
                mycent_subjective = out_count_byTeacher,
                count_total = (out_count_right + out_count_wrong + out_count_giveup + out_count_byTeacher)  ,        
                mycent = out_myTotalCent 
                    where id = in_id_paper_log ;                     

            set out_state = 1;                
            set out_msg = 'done';            

        end if;
    end if;
END;
