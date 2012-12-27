CREATE PROCEDURE `basic_group__import`(IN in_guid char(36),OUT out_state int,OUT out_msg varchar(200),OUT out_ids varchar(2000))
pro_main:BEGIN
/**
批量导入用户组信息

用户将一个EXCEL文件上传到系统,系统将EXCEL文件中的内容读取到 basic_excel 表
数据库存储过程分析basic_excel表中的内容,读取出业务数据,插入到各个业务表中

前提条件:
basic_excel 有待处理的业务数据

@param in_guid 标识一次EXCEL文件导入的唯一健
@param out_state 存储过程执行结果,1为正确通过,其他都表示错误.系统处理错误,2 3 4 为业务数据错误
@param out_msg 存储过程执行后返回的执行描述结果


@version 201212
@author wei1224hf@gmail.com
@qqgroup 135426431 
*/
    declare fig int;         
    declare rowindex__ int;
    declare A_,B_,C_,D_,E_,F_,G_ varchar(200);    
    declare code_,row1_,row2_ varchar(200); 
    declare id_creater_,id_creater_group_,rowindex_ int default 0;   
    declare code_creater_group_ varchar(200);     

    #内存表游标,用于模拟数组,处理EXCEL表头列,检验格式
    declare cur_array cursor for     
        SELECT code,row1,row2 from array_group;           
    #核心游标,处理业务数据
    declare cur_group cursor for 
        SELECT A,B,C,D,E,F,G,rowindex from basic_excel where sheetname = basic_memory__il8n('group','basic_group',1)         
            and guid = in_guid            
            and rowindex > 1
            order by rowindex;         
    #MYSQL游标必须得变量
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;       

    #如果guid是空的,就报错
    if in_guid is null then        
        set out_state = 0;        
        set out_msg = 'null guid';   
        insert into basic_log (type,username,msg) values (1,'system','basic_group__import wrong , no guid' );               
        leave pro_main;
    end if;
        
    #数据库内存表,用于实现数组,处理EXCEL表头列
    drop TEMPORARY table if exists array_group;
    create  TEMPORARY  table array_group (
        code varchar(2)        
        ,row1 varchar(200)   
        ,row2 varchar(200)        
    ) engine = memory ;         
    
    select A,B,C,D,E,F,G,id_creater into A_,B_,C_,D_,E_,F_,G_,id_creater_ from basic_excel 
        where guid = in_guid 
        and rowindex = 1 
        and sheetname = basic_memory__il8n('group','basic_group',1); 
    if A_ is null then    
        #EXCEL中缺少必要的 sheet 
        set out_msg = basic_memory__il8n('sheetMissing','basic_excel',1); 
        set out_state = 0;        
        insert into basic_log (type,username,msg) values (1,'system',out_msg );             
        leave pro_main;
    end if;       
    
    set out_state = 0;    
    set out_msg = "";    
    set out_ids = "";      

    insert into array_group values 
        ('A', basic_memory__il8n( A_,'basic_group', 2) ,A_ ),        
        ('B', basic_memory__il8n( B_,'basic_group', 2) ,B_ ),        
        ('C', basic_memory__il8n( C_,'basic_group', 2) ,C_ ),        
        ('D', basic_memory__il8n( D_,'basic_group', 2) ,D_ ),        
        ('E', basic_memory__il8n( E_,'basic_group', 2) ,E_ ),        
        ('F', basic_memory__il8n( F_,'basic_group', 2) ,F_ ),        
        ('G', basic_memory__il8n( G_,'basic_group', 2) ,G_ )
    ;
    #select * from array_group;
        
    #开始检查excel列结构
    set @sufficient = "name,code,type,remark";
    set @keys = "";    
    set @columns = "";
    set fig = 0;    
    open cur_array;        
    fetch cur_array into code_,row1_,row2_;        
    while( fig = 0 ) do     
        if row1_ is null then        
            set out_msg = concat(row2_," ",code_,"2"," ","wrong column");                             
        elseif FIND_IN_SET(row1_,@sufficient) = 0 then        
            #如果有一些不需要的列
            set out_msg = concat(row2_," ",code_,"2"," ","wrong column");                   
            set out_state = 2;  
            insert into basic_log (type,username,msg) values (1,'system',out_msg );             
            leave pro_main;                
        else                    
            #select code_;
            set @keys = concat(",",row1_,@keys);       
            set @columns = concat(",",code_,@columns);         
        end if;  
    fetch cur_array into code_,row1_,row2_;     
    end while;
    close cur_array;          

    #如果一些必需的列没有
    if FIND_IN_SET('name',@keys) = 0 then    
        set out_state = 0;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('name','basic_group',1));             
        insert into basic_log (type,username,msg) values (1,'system',out_msg );          
        leave pro_main;        
    elseif FIND_IN_SET('type',@keys) = 0 then    
        set out_state = 0;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('type','basic_group',1));             
        insert into basic_log (type,username,msg) values (1,'system',out_msg );        
        leave pro_main;        
    elseif FIND_IN_SET('code',@keys) = 0 then    
        set out_state = 0;
        set out_msg = concat(basic_memory__il8n('columnMissing','basic_excel',1)," ",basic_memory__il8n('code','basic_group',1));             
        insert into basic_log (type,username,msg) values (1,'system',out_msg );   
        leave pro_main;
    end if;     

    if id_creater_ = 1 then            
        #是超级管理员 admin 导入的数据        
        set id_creater_group_ = 1;        
        set code_creater_group_ = '10';
    else   
        select group_id,group_code into id_creater_group_,code_creater_group_ from basic_user where id =  id_creater_;
    end if;

    #select @keys;        
    #开始拼凑核心SQL语句
    set @keys = concat(@keys,",");        
    set @columns = concat(@columns,",");    
    set @columncount = basic_stringcount(@keys,",");    
    select max(rowindex) into @maxrow from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('group','basic_group',1);  
    set @sql_insert = concat("insert into basic_group (id",@keys,"status) values ");     
    set @sql_department_insert = " insert into basic_department (id,code,name,type,id_creater,id_creater_group,code_creater_group) values ";   

    set fig = 0;    
    open cur_group; 
    fetch cur_group into A_,B_,C_,D_,E_,F_,G_,rowindex_;    
    while ( fig = 0 ) do            
        #select A_,B_,C_,D_,E_,F_,G_,rowindex_;
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
            
            if @alphaindex = 'A' then set @tempvalue = A_; end if; 
            if @alphaindex = 'B' then set @tempvalue = B_; end if; 
            if @alphaindex = 'C' then set @tempvalue = C_; end if; 
            if @alphaindex = 'D' then set @tempvalue = D_; end if; 
            if @alphaindex = 'E' then set @tempvalue = E_; end if; 
            if @alphaindex = 'F' then set @tempvalue = F_; end if; 
            if @alphaindex = 'G' then set @tempvalue = G_; end if;          
            #select @alphaindex,@keyindex,@tempvalue,@columns;            

            if @keyindex = 'type' then   
                #检查此用户组类型是否正确        
                set @temp = null;
                select code into @temp from basic_memory where extend5 = 'basic_group__type' and extend4 = @tempvalue;            
                #select @temp,@tempvalu;
                if @temp is null then                 
                    set out_state = 3;
                    set out_msg = concat( basic_memory__il8n('wrongType','basic_group',1) ," ",@tempvalue," ",@alphaindex,rowindex_);                    
                    insert into basic_log (type,username,msg) values (1,'system',out_msg );   
                    leave pro_main;
                end if;     
                set @tempvalue = @temp;                  
                set @grouptype = @tempvalue;                
            elseif @keyindex = 'name' then                 
                set @groupname_temp = @tempvalue;      
            elseif @keyindex = 'code' then    
                #检查此用户组编码是否重复       
                set @codetemp = 0;       
                select count(*) into @codetemp from basic_group where code = @tempvalue ;                  
                if @codetemp > 0 then                                        
                    set out_state = 4;                        
                    set out_msg = concat(basic_memory__il8n('existCode','basic_group',1)," ",@tempvalue," ",@alphaindex,rowindex_);  
                    insert into basic_log (type,username,msg) values (1,'system',out_msg );       
                    leave pro_main;
                end if;                         
                if @grouptype = '2' then    
                    #如果是组织机构类型的编码            
                    set @sql_department_insert = concat(@sql_department_insert,"('",basic_memory__index('basic_department'),"','",@tempvalue,"','",@groupname_temp,"',1,'",id_creater_,"','",id_creater_group_,"','",code_creater_group_,"') ,");                    
                end if;                 
            end if;       
                     
            set @sql_values = concat(@sql_values,",'",@tempvalue,"'");
                     
            IF @p < @columncount-1 THEN
               ITERATE inerLoop;
            END IF;
            LEAVE inerLoop;            
        END LOOP inerLoop;          
        set @id = basic_memory__index('basic_group');   
        set out_ids = concat(out_ids,",",@id);       
        if rowindex_ = @maxrow then                 
            set @sql_insert = concat(@sql_insert,"('",@id,"'",@sql_values,",1) ;");            
        else
            set @sql_insert = concat(@sql_insert,"('",@id,"'",@sql_values,",1) ,");            
        end if;
        #select @sql_values;         
        #leave pro_main;
    fetch cur_group into A_,B_,C_,D_,E_,F_,G_,rowindex_;    
    end while;
    close cur_group;    

    #select @sql_insert;    

    PREPARE stmt FROM @sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;     

    set @sql_department_insert = SUBSTRING(@sql_department_insert,1,LENGTH(@sql_department_insert)-1);    
    #select @sql_department_insert;
    
    PREPARE stmt FROM @sql_department_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;      
    
    set out_state = 1;  
    set out_msg =  @sql_department_insert;

    delete from basic_excel where guid = in_guid;           
END;
