CREATE PROCEDURE `education_paper__export`(
in in_paperid int
,in in_excelid varchar(200)
,in in_sheetcount int
,in in_sheetindex int
,out out_state int
,out out_msg varchar(200)
,out out_excelid varchar(200)
,out out_sheetcount int
,out out_sheetindex int)
pro_main:BEGIN
/*
试卷导出

@version 201301
@author wei1224hf@gmail.com
*/
declare _paperid,        
        _rownum 
    int;    

declare _excelid 
    varchar(200);

    select id into _paperid from education_paper 
        where id = in_paperid limit 1;        
    if _paperid is null then        
        set out_state = 0;set out_msg = 'wrong paperid';
        leave pro_main;        
    end if;
  
    if in_excelid is null then
        set in_excelid = concat('',floor(rand()*100000));           
    end if;   
    set out_excelid = in_excelid;     

    if in_sheetindex is null then
        set in_sheetindex = 0;        
    end if;        
    set out_sheetindex = in_sheetindex + 1;        
        
    if in_sheetcount is null then
        set in_sheetcount = 1;        
    end if;      
    set out_sheetcount = in_sheetcount;

    insert into basic_excel (    
        guid
        ,sheets
        ,sheetindex
        ,sheetname
        ,rowindex
        ,maxcolumn

        ,A
        ,B
        ,C
        ,D
    ) values (         
        out_excelid 
        ,in_sheetcount        
        ,in_sheetindex     
        ,basic_memory__il8n('paper','education_paper',1)
        ,1
        ,4
        
        ,basic_memory__il8n('subject_code','education_paper',1)
        ,basic_memory__il8n('title','education_paper',1)
        ,basic_memory__il8n('cost','education_paper',1)
        ,basic_memory__il8n('teacher_name','education_paper',1)
        
    );
       
    insert into basic_excel (    
        guid
        ,sheets
        ,sheetindex
        ,sheetname
        ,rowindex
        ,maxcolumn

        ,A
        ,B
        ,C
        ,D   
    ) select         
        out_excelid
        ,in_sheetcount        
        ,in_sheetindex   
        ,basic_memory__il8n('paper','education_paper',1)
        ,2
        ,4
        
        ,subject_code
        ,title
        ,cost
        ,teacher_name                                                      
     from education_paper where id = in_paperid;     

     call education_question__export(in_paperid,in_excelid,(in_sheetcount+1),out_sheetindex,
          out_state,out_msg,out_excelid,out_sheetcount,out_sheetindex);

END;
