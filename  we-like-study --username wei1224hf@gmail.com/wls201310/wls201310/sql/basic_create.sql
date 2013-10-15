
drop table if exists  basic_parameter;
create table basic_parameter(
    code varchar(200) default '0', 
    value varchar(200) default '0', 
    reference varchar(200) default '0' ,
    id int auto_increment primary key, 
    extend1 int ,
    extend2 int ,
    extend3 int ,
    extend4 varchar(200) ,
    extend5 varchar(200) ,
    extend6 varchar(200) 
)CHARSET=utf8  ;

drop table if exists basic_user ;
create table basic_user (
     username varchar(200) default '0' unique 
    ,password varchar(200) default '0' 
   
    ,money int default '100' 
    ,credits int default 0  

    ,lastlogintime datetime default '1900-01-01'  
    ,lastlogouttime datetime default '1900-01-01' 
    ,count_actions int default '0' 
    ,count_actions_period int default '0'  
    ,count_login int default '0' 

    ,group_code varchar(20) default '0' 
    ,group_all varchar(200) default '0' 

    ,id int primary key  
    
    ,creater_code varchar(20) default '0' 
    ,creater_group_code varchar(20) default '0' 
    
    ,time_created timestamp default CURRENT_TIMESTAMP 
    ,time_lastupdated datetime default '1900-01-01' 
    ,count_updated int default 0 
    ,type int default '0' 
    ,status int default 1 
    ,remark text 

) CHARSET=utf8;

drop table if exists  basic_user_session ;
create table basic_user_session (

     user_id int primary key  
    ,user_code varchar(20) default '0'
    ,group_code varchar(20) default '0' 
    ,user_type varchar(200) default '0'
    ,permissions varchar(1000) 
    ,groups varchar(200) default '0'
    
    ,ip varchar(200) default '0' 
    ,client varchar(200) 
    ,gis_lat varchar(200)
    ,gis_lot varchar(200) 

    ,lastaction varchar(15) default '0'  
    ,lastactiontime datetime default '1900-01-01' 
    ,count_actions int default '0' 
    ,count_login int default '0' 

    ,session varchar(200) default '0' 
    ,status int default '0' 

) ENGINE=MEMORY CHARSET=utf8 ;

drop table if exists basic_memory ;
create table basic_memory (
     code varchar(200) default '0' 
    ,type int default '0'  
    ,extend1 int  
    ,extend2 int  
    ,extend3 int  
    ,extend4 varchar(200)
    ,extend5 varchar(200) 
    ,extend6 varchar(200) 
) ENGINE=MEMORY  CHARSET=utf8  ;

drop table if exists  basic_group;
create table basic_group(
     name varchar(200) default '0' 
    ,code varchar(200) unique  
    ,count_users int default '0' 

    ,id int primary key auto_increment 
    ,type int default '0' 
    ,status int default 1 
    ,remark varchar(500) default '0'
) CHARSET=utf8 ;


drop table if exists  basic_group_2_user ;
create table basic_group_2_user (
     user_code varchar(20) default '0' 
    ,group_code varchar(20) default '0' 

    ,UNIQUE KEY basic_g2u_u ( user_code,group_code )
    ,id int auto_increment primary key  
) CHARSET=utf8 ;

drop table if exists  basic_permission ;
create table basic_permission (
     name varchar(200) default '0' 
    ,type int default '0'  
    ,code varchar(200) unique 
    ,icon varchar(200) 
    ,path varchar(200) 

    ,id int primary key auto_increment 
    ,remark text
) CHARSET=utf8 ;

drop table if exists basic_group_2_permission;
create table basic_group_2_permission(
     permission_code varchar(20) default '0' not null    
    ,group_code varchar(20) default '0' not null 
    ,cost int default '0' 
    ,credits int default '0' 
    ,UNIQUE KEY basic_g2p_u ( permission_code,group_code )
    ,id int auto_increment primary key  
) CHARSET=utf8  ;
