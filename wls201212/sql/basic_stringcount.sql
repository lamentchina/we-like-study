CREATE FUNCTION `basic_stringcount`(
    f_str varchar(8000), f_substr varchar(255)) RETURNS int(11)
BEGIN
        #Created by david yeung 20080226.        

        declare i int default 0;
        declare remain_str varchar(8000) default '';
        set remain_str = f_str;
        while instr(remain_str,f_substr) > 0
        do
                set i = i + 1;
                set remain_str = substring(remain_str,instr(remain_str,f_substr) + char_length(f_substr));
        end while;
        return i;
END;
