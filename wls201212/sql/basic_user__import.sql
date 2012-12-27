CREATE PROCEDURE `basic_user__import`(IN in_guid char(36),OUT out_state int,OUT out_msg varchar(200),OUT out_ids varchar(2000))
pro_main:BEGIN
/**
批量导入用户信息

用户将一个EXCEL文件上传到系统,系统将EXCEL文件中的内容读取到 basic_excel 表
数据库存储过程分析basic_excel表中的内容,读取出业务数据,插入到各个业务表中


前提条件:
basic_group , basic_permission 表已经被创建并有数据
basic_excel 有待处理的业务数据

@param in_guid 标识一次EXCEL文件导入的唯一健
@param out_state 存储过程执行结果,1为正确通过,其他都表示错误.系统处理错误,2 3 4 为业务数据错误
@param out_msg 存储过程执行后返回的执行描述结果
@param out_ids 成功插入多个用户后,返回的用户表编号集

@version 201212
@author wei1224hf@gmail.com
@qqgroup 135426431 
*/
    declare fig int;         
    declare rowindex__ int;
    declare A_,B_,C_,D_,E_,F_,G_ varchar(200);    
    declare code_,row1_,row2_ varchar(200); 
    declare id_creater_,id_creater_group_,rowindex_ int default 0;        
    declare code_creater_group_ varchar(200) default '0';

    declare cur_array cursor for     
        SELECT code,row1,row2 from array_user;   
    declare cur cursor for 
        SELECT A,B,C,D,E,F,G,rowindex from basic_excel where sheetname = basic_memory__il8n('user','basic_user',1)         
            and guid = in_guid            
            and rowindex > 1
            order by rowindex;         
     
    #以下变量用于游标
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;       
    
    if in_guid is null then        
        set out_state = 0;        
        set out_msg = 'null guid';        
        insert into basic_log (type,username,msg) values (1,'system','basic_user__import wrong , no guid , line 44' );  
        leave pro_main;
    end if;
    
    drop TEMPORARY table if exists array_user;
    create  TEMPORARY  table array_user (
        code varchar(2)        
        ,row1 varchar(200)   
        ,row2 varchar(200)        
    ) engine = memory ;         
    
    select A,B,C,D,E,F,G,id_creater into A_,B_,C_,D_,E_,F_,G_,id_creater_ from basic_excel 
        where guid = in_guid 
        and rowindex = 1 
        and sheetname = basic_memory__il8n('user','basic_user',1); 
    if A_ is NULL then    
        set out_state = 0;        
        set out_msg = 'wrong guid';        
        leave pro_main;
    end if;     
    
    set out_state = 0;    
    set out_msg = "";    
    set out_ids = "";          

    set @sql_keys = '';        
    set @sql_keys_excel = '';    
    set @sql_values = '';
    insert into array_user values 
        ('A', basic_memory__il8n( A_,'basic_user', 2) ,A_ ),        
        ('B', basic_memory__il8n( B_,'basic_user', 2) ,B_ ),        
        ('C', basic_memory__il8n( C_,'basic_user', 2) ,C_ ),        
        ('D', basic_memory__il8n( D_,'basic_user', 2) ,D_ ),        
        ('E', basic_memory__il8n( E_,'basic_user', 2) ,E_ ),        
        ('F', basic_memory__il8n( F_,'basic_user', 2) ,F_ ),        
        ('G', basic_memory__il8n( G_,'basic_user', 2) ,G_ )
	;
    #select * from array_user;
	
	set @sufficient = "username,password,money,groupcode,type";
	set @keys = "";
	set @columns = "";
		
    set fig = 0;    
    open cur_array;        
    fetch cur_array into code_,row1_,row2_;        
    set @columnIndex = 1;
    while( fig = 0 ) do      
	
        if row1_ is null then        
			set out_msg = concat(row2_," ",code_,"1"," ","wrong column");                             
		elseif FIND_IN_SET(row1_,@sufficient) = 0 then             
			set out_msg = concat(row2_," ",code_,"1"," ","wrong column");                   
			set out_state = 0;    
            
			leave pro_main;                
		else            
			set @keys = concat(@keys,row1_,",");       
			set @columns = concat(@columns,code_,",");         
		end if;  

    fetch cur_array into code_,row1_,row2_;     
    end while;
    close cur_array;      
        
    set @keys = concat(",",@keys);  
    set @sql_insert = concat("insert into basic_user (id,person_id,id_group,id_creater_group,id_creater",@keys,"status ) values ");      
    set @user2group_sql_insert = "insert into basic_group_2_user ( id_user,id_group,type ) values ";    
    set @person_insert = "insert into basic_person (name,id) values ";    
    set @student_insert = "insert into education_student ( code,class_code,id,id_person,id_user ) values ";    
    set @teacher_insert = "insert into education_teacher ( code,department,id,id_person,id_user ) values ";    
    set @count_student = 0;
    set @count_teacher = 0;
    set @columns = concat(",",@columns);        
    set @columncount = basic_stringcount(@keys,",");   
    select max(rowindex) into @maxrow from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('user','basic_user',1);                

    set fig = 0;    
    open cur; 
    fetch cur into A_,B_,C_,D_,E_,F_,G_,rowindex_;
    cur_while: while (fig = 0) do      

        set @sql_values = '';  
        set @p = 0;         
        set @spotpos = 1;   
        set @spotpos2 = 1;         
        set @spotpos_ = 1;   
        set @spotpos2_ = 1;             
        set @tempvalue = ''; 
                
        set @id_person = basic_memory__index('basic_person');               
        set @id_user = basic_memory__index('basic_user');          
                       
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

            if @keyindex = 'username' then           
                set @temp = null;
                select count(username) into @temp from basic_user where username = @tempvalue;       
                #select @temp;
                if @temp > 0 then 
                    set out_state = 0;
                    set out_msg = @tempvalue;                    
                    
                    leave pro_main;
                end if;    
                set @username = @tempvalue;
            end if;                    
            if @keyindex = 'password' then           
                set @tempvalue = md5(@tempvalue);                    
            end if;    
            if @keyindex = 'groupcode' then           
                set @id_user_group = null;                              
                select id,code into @id_user_group,@code_user_group from basic_group where code = @tempvalue ;                    
                if @id_user_group is null then                                        
                    set out_state = 0;                        
                    set out_msg = concat('wrong ',@tempvalue);  
                                                       
                    leave pro_main;
                end if;                                         
            end if;  
            if @keyindex = 'type' then           
                set @temp = null;
                select code into @temp from basic_memory where extend5 = 'basic_user__type' and extend4 = @tempvalue;            
                #select @temp;
                if @temp is null then 
                    set out_state = 0;
                    set out_msg = @tempvalue;                    
                    delete from basic_excel where guid = in_guid;
                    leave pro_main;
                end if;          
                set @tempvalue = @temp;  
                
                if @temp = 2 then                
                    set @count_student = @count_student + 1;                    
                    set @student_insert = concat(@student_insert," ('",@username,"','"
                                                                    ,@code_user_group,"','"
                                                                    ,basic_memory__index('education_student'),"','"
                                                                    ,@id_person,"','"
                                                                    ,@id_user,"') ,");                                                                    
                    #select @student_insert;
                elseif @temp = 3 then                
                    set @count_teacher = @count_teacher + 1;                    
                    set @teacher_insert = concat(@teacher_insert," ('",@username,"','"
                                                                    ,@code_user_group,"','"
                                                                    ,basic_memory__index('education_teacher'),"','"
                                                                    ,@id_person,"','"
                                                                    ,@id_user,"') ,");
                end if;                         
            end if;                                            

            set @sql_values =  concat(@sql_values,",'",@tempvalue,"'");              

            IF @p < @columncount - 1 THEN
               ITERATE inerLoop;
            END IF;
            LEAVE inerLoop;
        END LOOP inerLoop;        
        
        set @sql_values = concat( @id_user ,",",@id_person,",",@id_user_group,",",id_creater_group_,",",id_creater_,@sql_values);        
        set out_ids = concat(out_ids,",",@id_user);

        if rowindex_ = @maxrow then                 
            set @sql_insert = concat(@sql_insert,"(",@sql_values,",1) ;");  
            set @user2group_sql_insert = concat(@user2group_sql_insert,"('",@id_user,"','",@id_user_group,"',1) ;");  
            set @person_insert = concat(@person_insert,"('",@username,"','",@id_person ,"') ;");  
        else        
            set @sql_insert = concat(@sql_insert,"(",@sql_values,",1) ,");  
            set @user2group_sql_insert = concat(@user2group_sql_insert,"('",@id_user,"','",@id_user_group,"',1) ,");            
            set @person_insert = concat(@person_insert,"('",@username,"','",@id_person ,"') ,");  
        end if;
            
    fetch cur into A_,B_,C_,D_,E_,F_,G_,rowindex_;
    end while cur_while;
    close cur;    
    
    PREPARE stmt FROM @sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;  
           
    PREPARE stmt FROM @user2group_sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;     
    set out_msg = @user2group_sql_insert;
           
    PREPARE stmt FROM @person_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;    
    
    if @count_student > 0 then    
        set @student_insert = SUBSTRING( @student_insert , 1 , LENGTH(@student_insert)-1 ) ;       
        #select @student_insert;
        PREPARE stmt FROM @student_insert;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;  
    end if;         
    if @count_teacher > 0 then    
        set @teacher_insert = SUBSTRING( @teacher_insert , 1 , LENGTH(@teacher_insert)-1 ) ;      
        #select @teacher_insert;  
        PREPARE stmt FROM @teacher_insert;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;  
    end if;         

    update basic_group set count_users = 
           (select count(*) from basic_user where basic_user.groupcode = basic_group.code  ) ;

    set out_state = 1;    
    #set out_msg = 'ok';    
    delete from basic_excel where guid = in_guid; 
end;
