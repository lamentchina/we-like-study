CREATE PROCEDURE `basic_group_2_permission__import`(IN in_guid char(36),OUT out_state int,OUT out_msg varchar(200))
pro_main:BEGIN
/**
用户将一个EXCEL文件上传到系统,系统将EXCEL文件中的内容读取到 basic_excel 表
数据库存储过程分析basic_excel表中的内容,读取出业务数据,插入到各个业务表中
用户组-权限 的对应关系处理


前提条件:
basic_group , basic_permission 表已经被创建并有数据
basic_excel 有待处理的业务数据

@param in_guid 标识一次EXCEL文件导入的唯一健
@param out_state 存储过程执行结果,1为正确通过,其他都表示错误.系统处理错误,2 3 4 为业务数据错误
@param out_msg 存储过程执行后返回的执行描述结果

@version 201212
@author wei1224hf@gmail.com
@qqgroup 135426431 
*/
    declare fig int;           
    declare rowindex_ int;    
    declare code_,row1_ varchar(200);       
    declare B_,C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_ varchar(200);          

    #内存表游标,模拟数组
    declare cur_array cursor for
        select code,row1 from array_g2p;       
    #核心业务游标    
    declare cur_g2p cursor for 
        SELECT B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,rowindex from basic_excel where sheetname = basic_memory__il8n('group2permission','basic_group_2_permission',1)         
            and guid = in_guid            
            and rowindex > 2 order by rowindex;   
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;       
        
    #如果 guid 是空的
    if in_guid is null then        
        set out_state = 0;        
        set out_msg = basic_memory__il8n('guidNULL','basic_excel',1);   
        insert into basic_log (type,username,msg) values (1,'system','basic_group_2_permission__import wrong , no guid , line 35' );            
        leave pro_main;
    end if;
        
    #初始化临时内存表,用于模拟数组,处理EXCEL表头列
    drop TEMPORARY  table if exists array_g2p;
    create TEMPORARY  table array_g2p (
        code varchar(2)        
        ,row1 varchar(200)         
    ) engine = memory ;      

    #检查第一行业务数据, C2 必定是 管理员用户组编码 10 ,如果空,则说明EXCEL错误
    select C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,maxcolumn into C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_,@maxcolumn from basic_excel 
        where guid = in_guid 
        and rowindex = 2 
        and sheetname = basic_memory__il8n('group2permission','basic_group_2_permission',1) ;  
    if C_ is null then          
        set out_state = 0;        
        set out_msg = basic_memory__il8n('guidWrong','basic_excel',1);        
        insert into basic_log (type,username,msg) values (1,'system','basic_group_2_permission__import wrong , key group cde 10 missing , line 54' );    
        leave pro_main;
    end if;    
    
    set out_state = "";
    set out_msg = "";  
        
    #开始检查用户组,判断用户组是否正确
    insert into array_g2p values 
        ('C',	C_),
        ('D',	D_),
        ('E',	E_),
        ('F',	F_),
        ('G',	G_),
        ('H',	H_),
        ('I',	I_),
        ('J',	J_),
        ('K',	K_),
        ('L',	L_),
        ('M',	M_),
        ('N',	N_),
        ('O',	O_),
        ('P',	P_),
        ('Q',	Q_),
        ('R',	R_),
        ('S',	S_),
        ('T',	T_),
        ('U',	U_),
        ('V',	V_);        
    #select * from array_g2p;        
    set @columns = '';    
    set @groups = '';    
    set @groupids = '';
    set fig = 0;    
    open cur_array;        
    fetch cur_array into code_,row1_;        
    set @columnIndex = 1;
    while_arr:while( fig = 0 ) do           
        if row1_ is null then        
            set out_msg = concat(out_msg,code_,'1','null;');     
            leave while_arr;           
        end if;                         
        set @temp = null;
        select id into @temp from basic_group where code = row1_;     
        #如果用户组不存在   
        if @temp is null then                
            set out_state = 2;     
            if row1_ is null then
                set row1_ = 'null';
            end if;       
            set out_msg = concat( basic_memory__il8n('wrongGroupcode','basic_group_2_permission',1), row1_ );          
            insert into basic_log (type,username,msg) values (1,'system','basic_group_2_permission__import wrong , group worng , line 108' );    
            leave pro_main;
        end if;
        set @columns = concat(@columns,",",code_);      
        set @groups = concat(@groups,",",row1_);              
        set @groupids = concat(@groupids,",",@temp);      
    fetch cur_array into code_,row1_;    
    end while while_arr;
    close cur_array;  
    
    set @columns = concat(@columns,",");    
    set @groupids = concat(@groupids,",");      
    set @groups = concat(@groups,",");  
    set @columncount = basic_stringcount(@columns,",");
    #select @columns,@groupids,@columncount;         

    #开始拼凑SQL语句,准备插入 用户组-权限 的对应关系
    select max(rowindex) into @maxrow from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('group2permission','basic_group_2_permission',1) ;             
    set @sql_insert = "insert into basic_group_2_permission (id_permission,id_group,code_permission,code_group,cost,credits) values ";    
    set @x = 0;
    set fig = 0;    
    open cur_g2p;            
    fetch cur_g2p into B_,C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_,rowindex_;    
    #开启游标,逐行检查数据
    cur_while: while (fig = 0) do    
        #先检查这一行的B列数据,也就是权限编码   
        set @temp = null;   
        select id into @temp from basic_permission where code = B_;        
        if @temp is null then                
            set out_state = 3;                        
            #select rowindex_,@sql_insert;            
            if row1_ is null then             
                set row1_ = 'null' ;                               
            end if;
            set out_msg = concat( basic_memory__il8n('wrongPermissioncode','basic_group_2_permission',1), row1_ );  
            insert into basic_log (type,username,msg) values (1,'system','basic_group_2_permission__import wrong , permission worng , line 141' );    
            leave pro_main;
        end if;                
        
        set @sql_values = '';  
        set @p = 0;         
        set @spotpos = 1;   
        set @spotpos2 = 1;   
        set @spotpos_ = 1;   
        set @spotpos2_ = 1;    
        set @spotpos__ = 1;   
        set @spotpos2__ = 1;                   
        set @tempvalue = '';    
        set @sql_values = '';  
        set @keyindex = '';     
        set @alphaindex = '';                                  

        #循环检查这一行每一个单元格里的内容
        inerLoop: LOOP
            SET @p = @p + 1;   
            set @spotpos = LOCATE(',', @columns,@spotpos2);
			set @spotpos2 = LOCATE(',', @columns,@spotpos+1);
            set @alphaindex = SUBSTRING(@columns,@spotpos+1,@spotpos2-@spotpos-1);                

            set @spotpos_ = LOCATE(',', @groupids,@spotpos2_);
			set @spotpos2_ = LOCATE(',', @groupids,@spotpos_+1);
            set @groupid = SUBSTRING(@groupids,@spotpos_+1,@spotpos2_-@spotpos_-1);   
            
            set @spotpos__ = LOCATE(',', @groups,@spotpos2__);
			set @spotpos2__ = LOCATE(',', @groups,@spotpos__+1);
            set @groupcode = SUBSTRING(@groups,@spotpos__+1,@spotpos2__-@spotpos__-1);             

            if @groupid is null or @groupid = '' then  
                set out_state = 4;                    
                set out_msg = 'worng id or code';
                leave pro_main;
            end if;         
		
            if @alphaindex = 'C' then set @tempvalue = C_; end if; 		
            if @alphaindex = 'D' then set @tempvalue = D_; end if; 		
            if @alphaindex = 'E' then set @tempvalue = E_; end if; 		
            if @alphaindex = 'F' then set @tempvalue = F_; end if; 		
            if @alphaindex = 'G' then set @tempvalue = G_; end if; 		
            if @alphaindex = 'H' then set @tempvalue = H_; end if; 		
            if @alphaindex = 'I' then set @tempvalue = I_; end if; 		
            if @alphaindex = 'J' then set @tempvalue = J_; end if; 		
            if @alphaindex = 'K' then set @tempvalue = K_; end if; 		
            if @alphaindex = 'L' then set @tempvalue = L_; end if; 		
            if @alphaindex = 'M' then set @tempvalue = M_; end if; 		
            if @alphaindex = 'N' then set @tempvalue = N_; end if; 		
            if @alphaindex = 'O' then set @tempvalue = O_; end if; 		
            if @alphaindex = 'P' then set @tempvalue = P_; end if; 		
            if @alphaindex = 'Q' then set @tempvalue = Q_; end if; 		
            if @alphaindex = 'R' then set @tempvalue = R_; end if; 		
            if @alphaindex = 'S' then set @tempvalue = S_; end if; 		
            if @alphaindex = 'T' then set @tempvalue = T_; end if; 		
            if @alphaindex = 'U' then set @tempvalue = U_; end if; 	            

            if (@tempvalue is null) or @tempvalue = '' then         
               set out_msg = '';               
            else    
               set @x = @x + 1;              
               set @sql_insert = concat(@sql_insert,"(",@temp,",",@groupid,",'",B_,"','",@groupcode,"',",@tempvalue,") ,");
            end if;   
  
            IF @p < @columncount-1 THEN       
               ITERATE inerLoop;
            END IF;
            LEAVE inerLoop;
        END LOOP inerLoop;          
     
    fetch cur_g2p into B_,C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_,rowindex_;
    end while cur_while;
    close cur_g2p;             

    #先删除原先已有的那些用户组原有的权限关系
    set @sql_delete = concat( "delete from basic_group_2_permission where id_group in (99999",@groupids,"99998);");    
    PREPARE stmt FROM @sql_delete;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;    
  
    set @sql_insert = SUBSTRING( @sql_insert , 1 , LENGTH(@sql_insert)-1 );    
    #select @x;
    #select @columns,@groups,@groupids;     
    #select @sql_insert;      

    PREPARE stmt FROM @sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;      
    set out_state = 1;    
    set out_msg = 'done';

    drop TEMPORARY table if exists array_g2p; 
    delete from basic_excel where guid = in_guid; 
END;
