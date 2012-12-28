CREATE PROCEDURE `basic_permission__import`(IN in_guid char(36),OUT out_state int,OUT out_msg varchar(200),OUT out_ids varchar(2000))
pro_main:BEGIN 
/*
权限导入
服务端上传一个 EXCEL 文件,并读取存储到 basic_excel 中
然后此存储过程分析业务数据插入到业务表

@version 201212
@author wei1224hf@gmail.com
*/
    declare fig int;         
    declare rowindex__ int;
    declare A_,B_,C_,D_,E_,F_,G_ varchar(200);    
    declare code_,row1_,row2_ varchar(200); 
    declare id_creater_,id_creater_permission_,rowindex_ int default 0;    

    declare cur_array cursor for     
        SELECT code,row1,row2 from array_permission;   
    declare cur_permission cursor for 
        SELECT A,B,C,D,E,F,G,rowindex from basic_excel where sheetname = basic_memory__il8n('permission','basic_permission',1)         
            and guid = in_guid            
            and rowindex > 1
            order by rowindex;         
     
    #以下变量用于游标
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;       
    
    if in_guid is null then        
        set out_state = 0;        
        set out_msg = basic_memory__il8n('guidNULL','basic_excel',1);    
        leave pro_main;
    end if;
    
    drop TEMPORARY table if exists array_permission;
    create  TEMPORARY  table array_permission (
        code varchar(2)        
        ,row1 varchar(200)   
        ,row2 varchar(200)        
    ) engine = memory ;         
    
    select A,B,C,D,E,F,G,id_creater into A_,B_,C_,D_,E_,F_,G_,id_creater_ from basic_excel 
        where guid = in_guid 
        and rowindex = 1 
        and sheetname = basic_memory__il8n('permission','basic_permission',1); 
    if A_ is null then
        set out_msg = basic_memory__il8n('guidWrong','basic_excel',1); 
        set out_state = 0;
        leave pro_main;
    end if;     
    
    set out_state = 0;    
    set out_msg = "";    
    set out_ids = "";                

    insert into array_permission values 
        ('A', basic_memory__il8n( A_,'basic_permission', 2) ,A_ ),        
        ('B', basic_memory__il8n( B_,'basic_permission', 2) ,B_ ),        
        ('C', basic_memory__il8n( C_,'basic_permission', 2) ,C_ ),        
        ('D', basic_memory__il8n( D_,'basic_permission', 2) ,D_ ),        
        ('E', basic_memory__il8n( E_,'basic_permission', 2) ,E_ ),        
        ('F', basic_memory__il8n( F_,'basic_permission', 2) ,F_ ),        
        ('G', basic_memory__il8n( G_,'basic_permission', 2) ,G_ )
    ;
    #select * from array_permission;
    
    set @sufficient = "name,code,type,remark,icon,path";
    set @keys = "";
    set @columns = "";
        
    set fig = 0;    
    open cur_array;        
    fetch cur_array into code_,row1_,row2_;        
    while( fig = 0 ) do      
    
        if row1_ is null then        
            set out_msg = concat(row2_," ",code_,"2"," ","wrong column");                             
        elseif FIND_IN_SET(row1_,@sufficient) = 0 then             
            set out_msg = concat(row2_," ",code_,"2"," ","wrong column");                   
            set out_state = 0;                
            leave pro_main;                
        else            
            set @keys = concat(",",row1_,@keys);       
            set @columns = concat(",",code_,@columns);         
        end if;  

    fetch cur_array into code_,row1_,row2_;     
    end while;
    close cur_array;      
    if FIND_IN_SET('name',@keys) = 0 then    
        set out_state = 7;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('name','basic_permission',1));             
        delete from basic_excel where guid = in_guid;        
        leave pro_main;        
    elseif FIND_IN_SET('type',@keys) = 0 then    
        set out_state = 0;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('type','basic_permission',1));             
        delete from basic_excel where guid = in_guid;        
        leave pro_main;        
    elseif FIND_IN_SET('code',@keys) = 0 then    
        set out_state = 0;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('code','basic_permission',1));             
        delete from basic_excel where guid = in_guid;        
        leave pro_main;
    end if; 

    #select @keys;    
    set @keys = concat(@keys,",");        
    set @columns = concat(@columns,",");    
    set @columncount = basic_stringcount(@keys,",");    
    select max(rowindex) into @maxrow from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('permission','basic_permission',1);  
    set @sql_insert = concat("insert into basic_permission (id",@keys,"status) values ");        

    set fig = 0;    
    open cur_permission; 
    fetch cur_permission into A_,B_,C_,D_,E_,F_,G_,rowindex_;    
    while ( fig = 0 ) do        
        set @sql_values = '';  
        set @p = 0;         
        set @spotpos = 1;   
        set @spotpos2 = 1;   
        set @spotpos_ = 1;   
        set @spotpos2_ = 1;             
        set @tempvalue = '';    
        set @student_sql_values = '';                            
        set @person_sql_values = '';           

        inerLoop: LOOP
            SET @p = @p + 1;  
            set @spotpos = LOCATE(',', @columns,@spotpos2);
            set @spotpos2 = LOCATE(',', @columns,@spotpos+1);
            set @alphaindex = SUBSTRING(@columns,@spotpos+1,@spotpos2-@spotpos-1);                

            set @spotpos_ = LOCATE(',', @keys,@spotpos2_);
            set @spotpos2_ = LOCATE(',', @keys,@spotpos_+1);
            set @keyindex = SUBSTRING(@keys,@spotpos_+1,@spotpos2_-@spotpos_-1); 
            
            #select @alphaindex,@keyindex;
            
            if @alphaindex = 'A' then set @tempvalue = A_; end if; 
            if @alphaindex = 'B' then set @tempvalue = B_; end if; 
            if @alphaindex = 'C' then set @tempvalue = C_; end if; 
            if @alphaindex = 'D' then set @tempvalue = D_; end if; 
            if @alphaindex = 'E' then set @tempvalue = E_; end if; 
            if @alphaindex = 'F' then set @tempvalue = F_; end if; 
            if @alphaindex = 'G' then set @tempvalue = G_; end if;             

            if @keyindex = 'type' then           
                set @temp = null;
                select code into @temp from basic_memory where extend5 = 'basic_permission__type' and extend4 = @tempvalue;            
                #select @temp;
                if @temp is null then 
                    set out_state = 0;
                    set out_msg = concat( basic_memory__il8n('wrongType','basic_permission',1) ," ",@tempvalue," ",@alphaindex,rowindex_);                    
                    delete from basic_excel where guid = in_guid;
                    leave pro_main;
                end if;     
                set @tempvalue = @temp;  
            elseif @keyindex = 'code' then           
                set @codetemp = 0;       
                select count(*) into @codetemp from basic_permission where code = @tempvalue ;                  
                if @codetemp > 0 then                                        
                    set out_state = 0;                        
                    set out_msg = concat(basic_memory__il8n('existCode','basic_permission',1)," ",@tempvalue," ",@alphaindex,rowindex_);  
                    delete from basic_excel where guid = in_guid;                   
                    leave pro_main;
                end if;                                               
            end if;       
                     
            set @sql_values = concat(@sql_values,",'",@tempvalue,"'");
                     
            IF @p < @columncount-1 THEN
               ITERATE inerLoop;
            END IF;
            LEAVE inerLoop;            
        END LOOP inerLoop;          
        set @id = basic_memory__index('basic_permission');   
        set out_ids = concat(out_ids,",",@id);       
        if rowindex_ = @maxrow then                 
            set @sql_insert = concat(@sql_insert,"('",@id,"'",@sql_values,",1) ;");            
        else
            set @sql_insert = concat(@sql_insert,"('",@id,"'",@sql_values,",1) ,");            
        end if;
        #select @sql_values;         
        #leave pro_main;
    fetch cur_permission into A_,B_,C_,D_,E_,F_,G_,rowindex_;    
    end while;
    close cur_permission;    

    #select @sql_insert;    

    PREPARE stmt FROM @sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;       
    set out_state = 1;    
    set out_msg = 'done';
    delete from basic_excel where guid = in_guid;         
END;
