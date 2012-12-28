CREATE FUNCTION `basic_randstring`(str_length int,str_type   int) RETURNS varchar(200)
BEGIN
 -- Function   : rand_string   
    -- Author     : reymondtu#opencfg.com   
    -- Date       : 2011/03/27   
    -- Params     : str_length int unsigned    
    --                  The random string length of random string   
    --              str_type   int unsigned   
    --                  The random string type   
    --                      1.0-9   
    --                      2.a-z   
    --                      3.A-Z   
    --                      4.a-zA-Z   
    --                      5.0-9a-zA-Z   
    --   
    -- Example    :   
    --   
    -- mysql> select rand_string(32,5) from dual;   
    -- +----------------------------------+   
    -- | rand_string(32,5)                |   
    -- +----------------------------------+   
    -- | HbPBz4DWSAiJNLt4SgExHVwQI34bI6mt |   
    -- +----------------------------------+   
    -- 1 row in set       

    declare counter int unsigned default 0;   
    declare const_chars varchar(64) default '0123456789';   
    declare result varchar(255) default '';   
    
    if str_type = 1 then  
        set const_chars = '0123456789';   
    elseif str_type = 2 then  
        set const_chars = 'abcdefghijklmnopqrstuvwxyz';   
    elseif str_type = 3 then  
        set const_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';   
    elseif str_type = 4 then  
        set const_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';   
    elseif str_type = 5 then  
        set const_chars = '0123456789abcdefghijklmnopqrstuvwxyz';   
    else  
        set const_chars = '0123456789';   
    end if;   
    
    while counter < str_length do     
        set result = concat(result,substr(const_chars,ceil(rand()*(length(const_chars)-1)),1));   
    set counter = counter + 1;   
    end while;   
  
    return result;   
END;
