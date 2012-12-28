CREATE FUNCTION `basic_memory__il8n`(in_key varchar(200),in_tablename varchar(200),flag int) RETURNS varchar(200)
BEGIN
/*
国际化语言读取函数
会被其他存储过程频繁调用的函数,
根据 basic_memory 表中存储的对应关系,读取对应的 健-值 国际化信息

国际化语言,最初是存储在服务端代码中的 language 文件夹中的一大堆 .ini中
当系统第一次运行时,这些 .ini 文件会被读取并保存在数据库表 basic_memeory 中

@version 201212
@author wei1224hf@gmail.com
*/
	declare out_value varchar(200) CHARACTER SET utf8;
    if in_key is null then
        return NULL;
    end if;    

    if flag = 1 then
        
        if in_tablename = 'normal' then    
            select extend4 into out_value from basic_memory where code = in_key and extend6 = 'il8n' and extend5 is null;        
        else    
            select extend4 into out_value from basic_memory where code = in_key and extend6 = 'il8n' and extend5 = in_tablename ;   
            if out_value is null then            
                select extend4 into out_value from basic_memory where code = in_key and extend6 = 'il8n' and extend5 is null;    
            end if;                             
        end if;        
    else 

        if in_tablename = 'normal' then    
            select code into out_value from basic_memory where extend4 = in_key and extend6 = 'il8n' and extend5 is null;        
        else    
            select code into out_value from basic_memory where extend4 = in_key and extend6 = 'il8n' and extend5 = in_tablename ;     
            if out_value is null then            
                select code into out_value from basic_memory where extend4 = in_key and extend6 = 'il8n' and extend5 is null;                  
                #set out_value = 'x';
                if ( (out_value is null) AND ( (in_tablename = 'education_student') or (in_tablename = 'education_teacher') ) ) then                
                    #set out_value = 'y';
                    select code into out_value from basic_memory where extend4 = in_key and extend6 = 'il8n' and extend5 = 'basic_person';                        
                end if;
            end if;           
        end if;  
    end if;
	RETURN out_value;
END;
