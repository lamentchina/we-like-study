drop table if exists exam_subject;
create table exam_subject(
    
     name varchar(200) default '0' 
    ,code varchar(200) unique  
    ,weight int default '0' 
    ,type int default '0' 

    ,id int primary key auto_increment 
    ,remark text 
) CHARSET=utf8  ;

drop table if exists  exam_subject_2_group ;
create table exam_subject_2_group (
     subject_code varchar(20) default '0' 
    ,group_code varchar(20) default '0' 

    ,UNIQUE KEY basic_g2u_u ( subject_code,group_code )
    ,id int auto_increment primary key  
)  CHARSET=utf8 ;

drop table if exists exam_subject_2_user_log ;
create table exam_subject_2_user_log (
     subject_code varchar(20) default '0' 
    ,count_positive int default '0' 
    ,count_negative int default '0' 
    ,proportion int default '0'
    ,paper_id int default '0' 
    ,paper_log_id int default '0' 
     
    ,id int auto_increment primary key      
    ,creater_code varchar(20) default '0' 
    ,creater_group_code varchar(20) default '0' 
    
    ,time_created timestamp default CURRENT_TIMESTAMP 

)  CHARSET=utf8 ;

drop table if exists exam_paper;
create table exam_paper(
    
     subject_code varchar(20) default '0'
    ,title varchar(200) default '0' 
    ,cost int default '0' 
    
    ,count_used int default '0' 
    ,cent_all  DECIMAL(8,2) default '0'
    ,cent  DECIMAL(6,2) default '0'
    ,cent_subjective  DECIMAL(6,2) default '0'
    ,cent_objective  DECIMAL(6,2) default '0'
    ,count_question int default '0' 
    ,count_subjective int default '0' 
    ,count_objective int default '0' 
    
    ,id int primary key  
    
    ,creater_code varchar(20) default '0' 
    ,creater_group_code varchar(20) default '0' 
    
    ,time_created timestamp default CURRENT_TIMESTAMP 
    ,time_lastupdated datetime default '1900-01-01' 
    ,count_updated int default 0 
    ,type int default '0' 
    ,status int default 1 
    ,remark text 
) CHARSET=utf8  ;

drop table if exists exam_paper_log;
create table exam_paper_log(
    
     mycent  DECIMAL(4,2) default '0'
    ,mycent_subjective  DECIMAL(4,2) default '0'
    ,mycent_objective  DECIMAL(4,2) default '0'
    ,count_right int default '0' 
    ,count_wrong int default '0' 
    ,count_giveup int default '0' 
    ,proportion int default '0' 
    
    ,paper_id int default '0'     
    ,id int primary key  
    
    ,creater_code varchar(20) default '0'
    ,creater_group_code varchar(20) default '0'
    ,status int default '10'
    ,time_created timestamp default CURRENT_TIMESTAMP 

) CHARSET=utf8  ;

drop table if exists exam_question;
create table exam_question(
    
     id int primary key  
    ,id_parent int default '0' 
    
    ,type int default '0' 
    ,subject_code varchar(20) default '0' 
    ,cent DECIMAL(4,2) default '2.0' 
    
    ,title text     
    ,option_length int default '0' 
    ,option_1 varchar(400) default '0' 
    ,option_2 varchar(400) default '0' 
    ,option_3 varchar(400) default '0' 
    ,option_4 varchar(400) default '0' 
    ,option_5 varchar(400) default '0' 
    ,option_6 varchar(400) default '0' 
    ,option_7 varchar(400) default '0' 
    
    ,answer varchar(200) default '0' 
    ,description varchar(500) default '0' 
    ,knowledge varchar(500) default '0' 
    ,difficulty int default '4' 
    ,path_listen varchar(200) default '0' 
    ,path_img varchar(200) default '0' 
    ,layout int default '1' 
    
    ,paper_id int default '0' 

) CHARSET=utf8  ;

drop table if exists exam_question_log;
create table exam_question_log(
     id int auto_increment primary key  
    ,paper_log_id int default '0' 
    ,question_id int default '0' 
    ,myanswer varchar(200) default '0' 
    ,mycent DECIMAL(4,2) default '0'
    ,img varchar(200) default '0' 
) CHARSET=utf8 ;

drop table if exists exam_question_log_wrongs;
create table exam_question_log_wrongs(    
     id int auto_increment primary key  
    ,question_id int 
    ,creater_code varchar(20) default '0' 
    ,time_created timestamp default CURRENT_TIMESTAMP 
    
    ,UNIQUE KEY `wrongs_key` (`question_id`,`creater_code`)
) CHARSET=utf8  ;


drop table if exists exam_paper_multionline;
create table exam_paper_multionline(    
     time_start datetime default '1900-01-01' 
    ,time_stop datetime default '1900-01-01' 
    ,passline int default '60' 
    ,paper_id int default '0' 
    
    ,count_total int default '0' 
    ,count_giveup int default '0' 
    ,count_passed int default '0' 
    ,count_failed int default '0' 
    ,proportion int default '0' 
    
    ,id int primary key  
    ,students varchar(5000) default '0' 
) CHARSET=utf8  ;