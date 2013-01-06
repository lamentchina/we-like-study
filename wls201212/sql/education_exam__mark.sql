CREATE PROCEDURE `education_exam__mark`(IN in_idexam int,OUT out_state int,OUT out_msg varchar(200),OUT out_passed int,out out_failed int, out out_giveup int)
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
declare maxid int;
    set out_passed = 0;    
    set out_failed = 0;    
    set out_giveup = 0;    

    #如果编号为空或者编号不存在,就报错
    if in_idexam is null then         
        set out_state = 0;        
        set out_msg = 'null id';        
        leave pro_main;
    end if;        
    set @temp = null;
    select id into @temp from education_exam where id = in_idexam;        
    if @temp is null then    
        set out_state = 0;        
        set out_msg = 'wrong id';        
        leave pro_main;        
    end if;        

    #如果所有人都旷考,就报错
    set @temp = null;
    select id_paper_log into @temp from education_exam_2_student where exam_id = in_idexam and id_paper_log > 0 limit 1;   
    if @temp is null then    
        set out_state = 0;        
        set out_msg = 'no submit';        
        leave pro_main;        
    end if;    

    #创建临时内存表,用于统计
    drop TEMPORARY table if exists array_mark;
    create TEMPORARY table array_mark (
        id int,        
        id2 int auto_increment primary key,        
        score int,        
        rank int,        
        studentid int,        
        studentname varchar(200)
    ) engine = memory ;     
        
    #因为MYSQL对嵌套游标支持不高,所以用内存表加自动递增来伪游标
    insert into array_mark(
        id
        ,studentid
        ,studentname
    ) (
        select 
            id_paper_log             
            ,id_creater            
            ,student_name
        from education_exam_2_student 
        where exam_id = in_idexam and id_paper_log > 0
    );        
    select max(id2) into maxid from array_mark;
    
    #依次批改每张试卷,模仿游标
    while( maxid > 0 ) do  
        select id into id_paper_log_ from array_mark where id2 = maxid; 
        set maxid = maxid - 1;    

        call education_paper__submit(id_paper_log_,
             @out_totalCent ,
             @out_myTotalCent ,
             @out_count_right ,
             @out_count_wrong ,
             @out_count_giveup ,
             @out_count_byTeacher ,
             @out_state ,
             @out_msg );   
                                                  
       #判断考试是否通过
       if @out_myTotalCent > 60 then       
           set @status = 41;   
           set out_passed = out_passed + 1;
       else       
           set @status = 42;       
           set out_failed = out_failed + 1;     
       end if;       
       update array_mark set score = @out_myTotalCent where id2 = maxid;      
                
       #更新单个学生的记录
       update education_exam_2_student set 
            score = @out_myTotalCent                    
            ,passline = '60'         
            ,totalcent = @out_totalCent            
            ,status = @status
            ,time_lastupdated = now()    
            ,count_updated = count_updated + 1 
       where id_paper_log = id_paper_log_;       

       update array_mark set score = @out_myTotalCent where id = id_paper_log_;
    end while;  

    #统计旷考人数
    select count(*) into out_giveup from education_exam_2_student 
        where id_paper_log = 0 and exam_id = in_idexam; 
    update education_exam_2_student set 
        score = '0'                
        ,passline = '60'         
        ,totalcent = '100'            
        ,status = '43'
        ,time_lastupdated = now()    
        ,count_updated = count_updated + 1 
    where id_paper_log = 0 and exam_id = in_idexam;      
        
    #更新试卷状态
    update education_exam set 
        status = 1  
        ,count_passed = out_passed
        ,count_students = (out_passed + out_failed)
        ,time_lastupdated = now()    
        ,count_updated = count_updated + 1 where id = in_idexam ;   
        
    #总排名      
    set @rownum = 0;
    update education_exam_2_student set rank = (@rownum:=@rownum+1)
        where exam_id = in_idexam and id_paper_log > 0 order by score desc; 
        
    #整理班级内排名    
    truncate table array_mark;    
    insert into array_mark(
        id
    ) (
        select 
            class_id             
        from education_exam_2_class 
        where exam_id = in_idexam 
    );        
    set maxid = 0;
    select max(id2) into maxid from array_mark;    

    #依次统计每个组,模仿游标
    while( maxid > 0 ) do     
        set maxid = maxid - 1;            
        set @rownum = 0;
        update education_exam_2_student set rank_class = (@rownum:=@rownum+1)
            where exam_id = in_idexam and id_paper_log > 0 
            and id_creater_group = (select id from array_mark where id2 = maxid) 
            order by score desc; 
    end while;                              

    set out_state = 1;set out_msg = 'OK';
    drop TEMPORARY table if exists array_mark;
END;
