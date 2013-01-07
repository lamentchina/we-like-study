CREATE PROCEDURE `education_question__import`(IN in_guid char(36),OUT out_state int,OUT out_msg varchar(200),OUT out_ids varchar(2000),OUT out_cent int,out out_count int)
pro_main:BEGIN
/*
前端上传一个 EXCEL 文件
服务端将EXCEL中的内容读取到 basic_excel 中
题目数据再从 basic_excel 读取到 education_question 中

@version 201209
@author wei1224hf@gmail.com
*/
    declare fig int;         
    declare rowindex__ int;
    declare A_,B_,C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_ varchar(200);    
    declare A__,B__,C__,D__,E__,F__,G__,H__,I__,J__,K__,L__,M__,N__,O__,P__,Q__,R__,S__,T__,U__,V__ varchar(2000);  
    declare columnsimport varchar(400) default ',type,type2,title,answer,optionlength,option1,option2,option3,option4,option5,option6,option7,description,cent,layout,id_parent,path_listen,path_image,subject,ids_level_knowledge,id_parent,remark';       
    declare code_ varchar(2);      
    declare row1_ varchar(200);    
    declare id_creater_ int default 0;    
    declare id_creater_group_ int default 0;           
    declare sql_insert varchar(8000);
    declare cur_array cursor for     
        SELECT code,row1 from array_question;   
    declare cur cursor for 
        SELECT A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,rowindex from basic_excel where sheetname = basic_memory__il8n('question','education_question',1)         
            and guid = in_guid            
            and rowindex > 1
            order by rowindex;     
            
    #以下变量用于游标
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET fig = 1;       
    
    if in_guid is null then        
        set out_state = 0;        
        set out_msg = 'null guid';
        leave pro_main;
    end if;
    
    drop TEMPORARY  table if exists array_question;
    create  TEMPORARY  table array_question (
        code varchar(2)        
        ,row1 varchar(200)   
        ,row2 varchar(200)        
    ) engine = memory ;         
    
    select A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,id_creater into A_,B_,C_,D_,E_,F_,G_,H_,I_,J_,K_,L_,M_,N_,O_,P_,Q_,R_,S_,T_,U_,V_,id_creater_ from basic_excel 
        where guid = in_guid 
        and rowindex = 1 
        and sheetname = basic_memory__il8n('question','education_question',1);       
    if A_ is null then          
        set out_state = 0;        
        set out_msg = 'wrong guid';
        leave pro_main;
    end if;   
    
    select id_creater,(select id_group from basic_user where id = basic_excel.id_creater ) into id_creater_,id_creater_group_  from basic_excel where guid = in_guid limit 1;
    if ( id_creater_ is null ) or  (id_creater_group_ is null ) then   
        set out_state = 0;
        set out_msg = 'id_user wrong';    
        leave pro_main;    
    end if;

    set @sql_keys = '';        
    set @sql_keys_excel = '';    
    set @sql_values = '';
    insert into array_question values 
        ('A', basic_memory__il8n( A_,'education_question', 2) ,A_ ),        
        ('B', basic_memory__il8n( B_,'education_question', 2) ,B_ ),        
        ('C', basic_memory__il8n( C_,'education_question', 2) ,C_ ),        
        ('D', basic_memory__il8n( D_,'education_question', 2) ,D_ ),        
        ('E', basic_memory__il8n( E_,'education_question', 2) ,E_ ),        
        ('F', basic_memory__il8n( F_,'education_question', 2) ,F_ ),        
        ('G', basic_memory__il8n( G_,'education_question', 2) ,G_ ),        
        ('H', basic_memory__il8n( H_,'education_question', 2) ,H_ ),              
        ('I', basic_memory__il8n( I_,'education_question', 2) ,I_ ),              
        ('J', basic_memory__il8n( J_,'education_question', 2) ,J_ ),              
        ('K', basic_memory__il8n( K_,'education_question', 2) ,K_ ),              
        ('L', basic_memory__il8n( L_,'education_question', 2) ,L_ ),              
        ('M', basic_memory__il8n( M_,'education_question', 2) ,M_ ),
		('N', basic_memory__il8n( N_,'education_question', 2) ,N_ ),
		('O', basic_memory__il8n( O_,'education_question', 2) ,O_ ),
		('P', basic_memory__il8n( P_,'education_question', 2) ,P_ ),
		('Q', basic_memory__il8n( Q_,'education_question', 2) ,Q_ ),
		('R', basic_memory__il8n( R_,'education_question', 2) ,R_ ),
		('S', basic_memory__il8n( S_,'education_question', 2) ,S_ ),
		('T', basic_memory__il8n( T_,'education_question', 2) ,T_ ),
		('U', basic_memory__il8n( U_,'education_question', 2) ,U_ ),
		('V', basic_memory__il8n( V_,'education_question', 2) ,V_ )
	;
    #select * from array_question;
		
    set fig = 0;    
    open cur_array;        
    fetch cur_array into code_,row1_;        
    set @columnIndex = 1;
    while( fig = 0 ) do           
        if row1_ is null then        
            set out_msg = concat(out_msg,code_,'1','null;');                
        elseif FIND_IN_SET(row1_,columnsimport) = 0 then 
            #select 4;    
            #select concat(row1_,columnsimport);            
            #select FIND_IN_SET(row1_,columnsimport);
            set out_msg = concat(out_msg,code_,'1','cant;');  
        else  
            #select code_,row1_;     
            set @columnIndex = @columnIndex + 1; 
            set @sql_keys = concat(@sql_keys,row1_,",");            
            set @sql_keys_excel = concat(@sql_keys_excel,code_,",");            

            if row1_ = 'type' then        
                set @columnIndexType = @columnIndex;                
            end if;            
            if row1_ = 'subject' then        
                set @columnIndexSubject = @columnIndex;                
            end if;            
            if row1_ = 'layout' then        
                set @columnIndexLayout = @columnIndex;                
            end if;            
            if row1_ = 'cent' then        
                set @columnIndexCent = @columnIndex;                
            end if;            
            if row1_ = 'optionlength' then        
                set @columnIndexOptionlength = @columnIndex;                
            end if;            
            if row1_ = 'answer' then        
                set @columnIndexAnswer = @columnIndex;                
            end if;
        end if;         

    fetch cur_array into code_,row1_;    
    end while;
    close cur_array;       
    #select @sql_keys,@sql_keys_excel;    
    set @columncount = basic_stringcount(@sql_keys_excel,",");    

    set @sql_insert = concat("insert into education_question (id,id_creater,id_creater_group",",",SUBSTRING( @sql_keys , 1 , LENGTH(@sql_keys)-1 ),") values ");       

    set out_ids = '';   
    set out_count = 0; 
    set out_cent = 0;    
    select max(rowindex) into @maxrow from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1);             
    set fig = 0;    
    open cur;            
    fetch cur into A__,B__,C__,D__,E__,F__,G__,H__,I__,J__,K__,L__,M__,N__,O__,P__,Q__,R__,S__,T__,U__,V__,rowindex__;
    cur_while: while (fig = 0) do  
        set @sql_values = '';  
        set @p = 0;         
        set @spotpos = 1;        
        set @tempvalue = '';                
        set @questionType = null;
        inerLoop: LOOP
            SET @p = @p + 1;  
            set @spotpos = LOCATE(',', @sql_keys_excel,@spotpos+1);
            set @alphaindex = MID(@sql_keys_excel,@spotpos-1,1);            
            #select @alphaindex,@spotpos;  

            if @alphaindex = 'A' then set @tempvalue = A__; end if; 
            if @alphaindex = 'B' then set @tempvalue = B__; end if; 
            if @alphaindex = 'C' then set @tempvalue = C__; end if; 
            if @alphaindex = 'D' then set @tempvalue = D__; end if; 
            if @alphaindex = 'E' then set @tempvalue = E__; end if; 
            if @alphaindex = 'F' then set @tempvalue = F__; end if; 
            if @alphaindex = 'G' then set @tempvalue = G__; end if; 
            if @alphaindex = 'H' then set @tempvalue = H__; end if; 
            if @alphaindex = 'I' then set @tempvalue = I__; end if; 
            if @alphaindex = 'J' then set @tempvalue = J__; end if; 
            if @alphaindex = 'K' then set @tempvalue = K__; end if; 
            if @alphaindex = 'L' then set @tempvalue = L__; end if; 
            if @alphaindex = 'M' then set @tempvalue = M__; end if; 
            if @alphaindex = 'N' then set @tempvalue = N__; end if; 
            if @alphaindex = 'O' then set @tempvalue = O__; end if; 
            if @alphaindex = 'P' then set @tempvalue = P__; end if; 
            if @alphaindex = 'Q' then set @tempvalue = Q__; end if; 
            if @alphaindex = 'R' then set @tempvalue = R__; end if; 
            if @alphaindex = 'S' then set @tempvalue = S__; end if; 
            if @alphaindex = 'T' then set @tempvalue = T__; end if; 
            if @alphaindex = 'U' then set @tempvalue = U__; end if; 
            if @alphaindex = 'V' then set @tempvalue = V__; end if; 
                  
            if @columnIndexType = @p+1 then             
                #select @tempvalue,@columnIndexType;                
                set @temp = null;
                select code into @temp from basic_memory where extend5 = 'education_question__type' and extend4 = @tempvalue;                
                #select @temp;
                if @temp is null then 
                    set out_state = 0;
                    set out_msg = concat('wrong type ',rowindex__);                    
                    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
                    leave pro_main;
                end if;                 
                set @tempvalue = @temp;                
                set @questionType = @temp;
            elseif @columnIndexSubject = @p+1 then            
                set @temp = null;
                select code into @temp from education_subject where code = @tempvalue;                
                #select @temp;
                if @temp is null then 
                    set out_state = 0;
                    set out_msg = 'wrong subject';                    
                    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
                    leave pro_main;
                end if;                 
            elseif @columnIndexLayout = @p+1 then            
                set @temp = null;
                select code into @temp from basic_memory where extend5 = 'education_question__layout' and extend4 = @tempvalue;            
                #select @temp;
                if @temp is null then 
                    set out_state = 0;
                    set out_msg = 'wrong layout';                    
                    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
                    leave pro_main;
                end if;                 
            elseif @columnIndexCent = @p+1 then            
                select ( @tempvalue REGEXP   '^[0-9]+$' ) into @temp ;
                if @temp = 0 then 
                    set out_state = 0;
                    set out_msg = concat('wrong cent ',rowindex__);                    
                    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
                    leave pro_main;
                end if;          
                set out_cent = out_cent + @tempvalue;
            elseif @columnIndexOptionlength = @p+1 then            
                select ( @tempvalue REGEXP   '^[0-7]$' ) into @temp ;
                if @temp = 0 then 
                    set out_state = 0;
                    set out_msg = concat('wrong length ',rowindex__);                    
                    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
                    leave pro_main;
                end if;                
            elseif @columnIndexAnswer = @p+1 then              
                set @quesTypeReg = '';
                if @questionType = 1 then                                
                   set @quesTypeReg = '^[ABCDEFG]$';
                elseif @questionType = 2 then     
                   set @quesTypeReg = '^(A,)?(B,)?(C,)?(D,)?(E,)?(F,)?[A-G]{1}$';     
                   #select       @quesTypeReg;
                elseif @questionType = 3 then                      
                   set @quesTypeReg = '^[AB]$';
                end if;     
                if @questionType < 4 then       
                    select ( @tempvalue REGEXP @quesTypeReg ) into @temp ;
                    if @temp = 0 then 
                        set out_state = 0;
                        set out_msg = concat('wrong answer ',rowindex__);
                        leave pro_main;
                    end if;                    
                end if;
            end if;    
            set @sql_values = concat(@sql_values,",'",@tempvalue,"'");              
            
            IF @p < @columncount THEN
               ITERATE inerLoop;
            END IF;
            LEAVE inerLoop;
        END LOOP inerLoop;        
        #leave pro_main;        
        #select @sql_values;        
               
        set @id_education_question = basic_memory__index('education_question');        
        set out_ids = concat(out_ids,",", @id_education_question);
        set @sql_values = concat(@id_education_question,",",id_creater_,",",id_creater_group_,@sql_values);  
        #select @sql_values;                        
        if rowindex__ = @maxrow then                 
            set @sql_insert = concat(@sql_insert,"(",@sql_values,") ");             
        else
            set @sql_insert = concat(@sql_insert,"(",@sql_values,") ,");             
        end if;        
        set out_count = out_count + 1;
        #select sql_insert;
        #leave pro_main;
    fetch cur into A__,B__,C__,D__,E__,F__,G__,H__,I__,J__,K__,L__,M__,N__,O__,P__,Q__,R__,S__,T__,U__,V__,rowindex__;
    end while cur_while;
    close cur;        

    set out_ids = SUBSTRING( out_ids , 2  ) ;
    #select @sql_insert;    leave pro_main;
    PREPARE stmt FROM @sql_insert;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;  
    set out_state = 1;
    set out_msg = 'ok';  
    drop TEMPORARY  table if exists array_question;
    delete from basic_excel where guid = in_guid and sheetname = basic_memory__il8n('question','education_question',1) ;
END;
