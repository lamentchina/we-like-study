--组织机构与权限设计
/*清空回收站  
drop table nst_standards
drop table nst_person
delete from nst_person
delete from nst_standards
PURGE RECYCLEBIN 
*/

/*组织机构是每个公司都有的,是架构业务系统的基本
组织机构编码,国家设置了一个标准用来给每个企事业单位都设置一个长编号,唯一的编号
*/
create table nst_organization (
        guid      varchar(200)        primary key --主键,使用 SYS-ID,可以产生一个全球唯一的编号,不知道数据库能否产生
       ,key       varchar(200)        unique      --组织机构编码,有层级关系的编码,可以组成树形结构
       ,name      varchar(200)        not null    --组织机构名称
       ,remark    varchar2(4000)                  --组织机构描述,就是一个简洁,可能要存储 HTML 字符串
)

/*人员表,用来描述现实世界中存在的个人单位
*/
create table nst_person(
        guid     varchar(200)         primary key --主键,使用 SYS-ID
       ,name     varchar(200)                     --姓名,有些系统还会采用 first-name , last-name 来录入姓名数据,难以处理
       ,gender   int                           --参考国标文件对性别的定义: 0 未知,1男,2女,9其他性别
       ,birthday date                             --生日
       ,birthplace     varchar(200)               --出生地
       ,nationality    varchar(200)               --国籍
       ,nation   varchar(200)                     --名族
       ,height   int                              --身高
       ,blood    int                              --血型
       
       ,photo    varchar(300)                     --照片 URL 路径
       
       ,degree   varchar(200)                     --学历
       ,school   varchar(200)                     --最高学历对应的毕业学校
       ,character      varchar(200)               --性格
       ,religion       int                        --宗教信仰
       
       ,phone    varchar(200)                     --电话联系方式
       ,email    varchar(200)                     --电子邮件联系方式
       ,qq       varchar(200)                     --QQ联系方式
       ,web      varchar(200)                     --个人网页,如果有的话
)

/*标准数据库,用来存储各个标准数据,比如行政区划编码,变动很小的那种
*/
create table nst_standards (
     code             varchar(200) not null
    ,value            varchar(200) not null
    ,source           varchar(200) not null
    ,remark           varchar2(400) 
    
    ,txt1             varchar(200) 
    ,txt2             varchar(200) 
    ,txt3             varchar(200) 
    ,txt4             varchar(200) 
    
)
comment on table  nst_standards is                       '标准';       
comment on column nst_standards.code is                  '编码';
comment on column nst_standards.value is                 '值';
comment on column nst_standards.source is                '来源';
comment on column nst_standards.remark is                '批注';


      

      
PURGE RECYCLEBIN