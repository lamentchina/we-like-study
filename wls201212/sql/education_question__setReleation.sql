CREATE PROCEDURE `education_question__setRelation`(id_paper_input int)
BEGIN
/*
将一张试卷从EXCEL文件导入到数据库的时候,
如果这张试卷中有 编号 这一列的话,那么导入的时候比较麻烦.
因为数据库中的 编号 这一列,是自动生成的. 
也就是说,EXCEL文件中的 编号 一列,不能算数
在导入的过程中,会先将试卷中 编号 一列,存储到 comment_ywrong_1 中,将 上级编号 存储到 comment_ywrong_2 中


然后再用这个存储过程,将数据库表中的 id_parent 更新

*/
    declare fig int ;
    declare id_parent_ int;
    declare id_ int;    
    declare cent_ int;    
    declare type_ int;    
    declare totalCent int default 0;    
    declare count_questions_ int default 0;
    declare cur cursor for 
        select comment_ywrong_2,id,cent,type from education_question where id_paper=id_paper_input  order by id   ;
    declare continue handler for not found set fig = 1;
   #将这个ID所对应的所有题目原先的 编号 上级编号 存储到一张临时表中,临时表会在这个存储过程结束之后直接清掉




    create TEMPORARY  table tmp  as
       select id,id_parent,comment_ywrong_2,comment_ywrong_1 from education_question where id_paper = id_paper_input;
    set id_parent_ = 0;
    set id_ = 0;

    open cur;
    repeat
    fetch cur into id_parent_,id_,cent_,type_ ;
        if id_parent_ != 0  then
            update education_question set id_parent = (select id from tmp where tmp.comment_ywrong_1 = id_parent_ limit 1) where id = id_;
        end if;        
        if (type_ = 1 || type_ = 2 || type_ = 3 || ( type_ = 4 and cent_ <> 0 ) || type_ = 6 ) then                
            set count_questions_ = count_questions_ + 1;            
            set totalCent = totalCent + cent_ ;
        end if;
    until fig = 1 end repeat;
    close cur;
    update education_question set 
        comment_ywrong_2 = 0 , 
        comment_ywrong_1 = 0 
            where id_paper = id_paper_input ;            
                
    update education_paper set     
        cent = totalCent,        
        count_questions = count_questions_        
            where id = id_paper_input;

END;
