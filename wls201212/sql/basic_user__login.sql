CREATE PROCEDURE `basic_user__login`(IN in_username varchar(200), IN in_password varchar(200), IN in_ip varchar(200), OUT out_msg varchar(200), OUT out_state int )
pro_main:BEGIN
/**
 登录操作后产生的用户 session 信息将被保存在数据库内存表中 
 取消对服务端 session 的依赖 
 便于对系统单点登录的判断,以及多服务器负载均衡的实现 

 数据库session表是一张内存表,里面记录每一个用户的系统操作次数 ,登录IP, 当天登录次数,当天正常退出次数 
 这些 统计次数 数据,会在系统自检过程中,累加到磁盘数据表中 

 用户的登录操作,采用的是简单的 MD5+时间戳 加密,时间戳为当前系统小时,有效验证时间为2小时 
 在系统前端,这个 session 会每隔15分钟更新一次,以保证前端保持更新

 version: 201210 
 author: wei1224hf@gmail.com  
 prerequisites: basic_memory__init,basic_memory.il8n()
 server used: basic_user.login() 
 involve: basic_user,basic_group,basic_permission,basic_group_2_user,basic_group_2_permission 
          basic_randstring,basic_memory__il8n,basic_user__group,basic_user__permission
 */
    declare username_ varchar(200);        
    declare id_user_ int;    
    declare password_ varchar(200);
    declare session_ varchar(200);        
    declare return_session_ varchar(32);    
    declare permissions_ varchar(1000);    
    declare mymoney_ int;    
    declare cost_ int;    
    declare credits_ int;

    select username,password,id,group_code,money into username_,password_,id_user_,@id_group,mymoney_ from basic_user where username = in_username;        
    if TRIM( username_ ) is NULL then    
        #用户不存在     
        set out_state = 0;
        set out_msg = 'no such user';   
        
        #记录这一次异常的登录事件,这有可能是嗅探工具
        insert into basic_log (msg,username,type) values ( concat('no such user; unm:' ,in_username, '; pwd: ', in_password,'; ip:',in_ip),'system',2);            
    else     
        #用户存在,判断密码 , 有2个小时的延时允许                  
        if  (
             ( in_password = md5( concat(TRIM(password_  ), ( hour(now()) - 0 ) ) ) ) or             
             ( in_password = md5( concat(TRIM(password_  ), ( hour(now()) - 1 ) ) ) ) or             
             ( in_password = md5( concat(TRIM(password_  ), ( hour(now()) - 2 ) ) ) )             
            )then              
                       
            #密码正确, 判断用户是否有足够的金币来执行登录             
            select min(cost),max(credits) into cost_,credits_  from 
            (
                SELECT
                basic_group_2_permission.cost,
                basic_group_2_permission.credits
                FROM
                basic_group_2_permission
                Left Join basic_group_2_user ON basic_group_2_permission.id_group = basic_group_2_user.id_group
                Left Join basic_user ON basic_group_2_user.id_user = basic_user.id
                Left Join basic_group ON basic_group_2_user.id_group = basic_group.id
                WHERE
                basic_group.code =  '10' AND
                basic_user.username =  in_username
            ) t;   
            #select cost_ , credits_,mymoney_;

            if mymoney_ < cost_ then                            
               set out_state = 2 ;
               set out_msg = concat('need money:',cost_,'; but I have:', mymoney_);                               
            else   
                update basic_user set money = money - cost_, money2 = money2 + credits_ where username = in_username;                  
                #登陆操作,先判断内存表中是否有这个人的记录了        
                select session into session_ from basic_user_session where username = in_username; 
           
                if session_ is NULL then                        
                    #如果内存表中没有记录,就要新插入一条数据   
                    set return_session_ = basic_randstring(32,5);                
                    set permissions_ = basic_user__permission(in_username);                    
                    #select permissions_;leave pro_main;
                    insert into basic_user_session(id_user,id_group,username,ip,session,permissions,lastactiontime,lastaction,groups,status) 
                        values (id_user_,@id_group,username_,in_ip,return_session_,permissions_,now(),'login',basic_user__group(in_username),1);    
                        
                    update basic_user set lastlogintime = now() where username = in_username;            
                    set out_msg = return_session_;                               
                    set out_state = 1;                
    
                else                
                    if in_username = 'guest' then    
                        select session into return_session_ from basic_user_session where username = in_username;                                        
                    else                    
                        #后续登陆的人,会将前面登陆的人T掉,会更新 session  
                        set return_session_ = basic_randstring(32,5);                     
                        update basic_user_session set session = return_session_,lastaction='login',lastactiontime=now(),status=1,count_login = count_login + 1, count_actions = count_actions + 1 where username = in_username;
                     end if;            
                     set out_msg = return_session_;                             
                     set out_state = 1;  
                end if;            

                #往日志表中插入一条记录,非正常登录
                insert into basic_log (msg,username,type) values ('unusual login',in_username,1); 
                set out_state = 1;                         
                #set out_msg = concat('money cost ',cost_,'; now I have ', mymoney_ - cost_ );                                      
                set out_msg = return_session_;
            end if;                
            
        else
            set out_msg = 'wrong password';                    
            set out_state = 0;            
        end if;        
    end if;
END;
