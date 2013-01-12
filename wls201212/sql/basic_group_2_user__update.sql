CREATE PROCEDURE `basic_group_2_user__update`(
in in_username varchar(200)
,in in_groupcodes varchar(200)
,out out_state int
,out out_msg varchar(200)
)
pro_main:BEGIN
/*
更新一个用户的用户组


@version 201301
@author wei1224hf@gmail.com
*/
declare _hasmain,
        _grouptype,        
        _groupid,        
        _userid,
        _groupcount int ;
declare _groupcode,
        _groupname varchar(100) ;

    if in_username = 'admin' or in_username = 'guest' or in_username is null then        
        set out_state = 0;        
        set out_msg = 'username wrong';        
        leave pro_main;
    end if;    
    select id into _userid from basic_user where username = in_username;    
    if _userid is NULL then        
        set out_state = 10;        
        set out_msg = 'username wrong';        
        leave pro_main;
    end if;
    START TRANSACTION;       
    delete from basic_group_2_user where username = in_username; 
    set _groupcount = basic_stringcount( in_groupcodes , ',' ) + 1;                
    set @pos = 0;        
    set @loop = 0;
    while _groupcount > 0 do        
        set @pos2 = LOCATE(',', in_groupcodes,@pos+1);  
        if @pos2 = 0 then        
            set _groupcode = SUBSTRING(in_groupcodes,@pos+1,char_length(in_groupcodes));                  
        else         
            set _groupcode = SUBSTRING(in_groupcodes,@pos+1,@pos2-1-@pos);                  
            set @pos = @pos2;             
        end if;   
        set _grouptype = NULL;
        select type,id,name into _grouptype,_groupid,_groupname from basic_group where code = _groupcode;        
        if _grouptype is NULL then                
            set out_state = 2;            
            set out_msg = 'wrong groupcode';                        
            rollback;
            leave pro_main;            
        elseif _grouptype = 2 then        
            if _hasmain = 1 then                        
                set out_state = 3;                
                set out_msg = 'one department only';                                
                rollback;
                leave pro_main;                
            else     
                update basic_user set
                    group_id = _groupid
                    ,group_name = _groupname
                    ,group_code = _groupcode  where username = in_username;       
                set _hasmain = 1;
            end if;
        end if;        
        insert into basic_group_2_user (
               username
               ,code_group
               ,id_user
               ,id_group
        ) values (
               in_username               
               ,_groupcode               
               ,_userid        
               ,_groupid
        );
        set _groupcount = _groupcount - 1;
    end while;  
    update basic_user set group_all = in_groupcodes where username = in_username;           
    COMMIT;    

    set out_state = 1;    
    set out_msg = 'OK';
END;
