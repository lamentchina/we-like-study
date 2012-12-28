CREATE PROCEDURE `basic_user__action`(IN `in_username` varchar(200),IN `in_session` varchar(200),IN in_action varchar(32),OUT out_state int,OUT out_msg varchar(200) )
BEGIN
/*
用户在前端每一次操作,
如果那个操作是设计扣费或者记积分的,
都会调用此存储过程

用户的 积分-金币 策略,会根据用户组的关系来读取出 最该加积分-最低扣金币 的关系

@author wei1224hf@gmail.com
@version 201209
*/
    declare username_ varchar(200);
    declare session_ varchar(200);        
    declare permissions_ varchar(1000);    
    declare actionsub char(2);    
    declare cost_ int;    
    declare credits_ int;    
    declare mymoney_ int;

    select session,permissions into session_,permissions_ from basic_user_session where username = in_username;                  
    if session_ is null then
        set out_msg = 'no login';                    
        set out_state = 0;                    
        
    elseif ( in_session  = md5( concat(session_, hour(now())-0) ) )or                                                
           ( in_session  = md5( concat(session_, hour(now())-2) ) )or                       
           ( in_session  = md5( concat(session_, hour(now())-1) ) ) then  
        #这个用户的 session 是符合条件的,接下来要判断这个用户有没有这个权限了    
        if  locate(concat(',',in_action,','), permissions_) = 0 then                    
            set out_state = 0;                         
            set out_msg = 'access denied';       
        else      
            #拥有这个权限,那么就需要更新 session 表     
            update basic_user_session set 
                   lastaction = in_action
                   ,lastactiontime=now()
                   ,count_actions = count_actions + 1
            where username = in_username;     
            
            #如果这个操作是涉及扣金币的话 
            set actionsub = right(in_action,2);    
            if actionsub > '09' then                        
               select min(cost),max(credits) into cost_,credits_  from 
                (
                SELECT
                basic_group_2_permission.cost,
                basic_group_2_permission.credits
                FROM
                    basic_permission
                    Right Join basic_group_2_permission ON basic_permission.id = basic_group_2_permission.id_permission
                    Right Join basic_group_2_user ON basic_group_2_permission.id_group = basic_group_2_user.id_group
                    Right Join basic_user ON basic_group_2_user.id_user = basic_user.id
                WHERE
                basic_permission.code =  in_action AND
                basic_user.username =  in_username
                ) t;                            

                select money into mymoney_ from basic_user where username = in_username;                            
                if mymoney_ < cost_ then                            
                   set out_state = 2 ;
                   set out_msg = concat('need money:',cost_,'; but I have:', mymoney_);                               
                else   
                    update basic_user set money = money - cost_, money2 = money2 + credits_ where username = in_username;  
                    set out_state = 1;                         
                    set out_msg = concat('money cost ',cost_,'; now I have ', mymoney_ - cost_ );                      
                end if;                
            else         
                #如果这个操作不必扣金币,就直接输出. 一般情况下 , 查询 查看 等常用的功能,都是这样的   
                set out_state = 1;                         
                set out_msg = 'success';     
            end if;  
        end if;
    else        
        set out_msg = 'wrong session';                                     
        #set out_msg = concat( md5( concat(session_, hour(now())-0) )  , ' ', md5( concat(session_, hour(now())-1) )  , ' ', md5( concat(session_, hour(now())-2) ), ' ', md5( concat(session_, hour(now())+1) )  );
        set out_state = 0;                    
    end if;                
END;
