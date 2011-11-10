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
在设计人员表以及其他实体表的时候，需要注意实体属性的类型。一般可以分成三类：
    1自然属性，与该实体紧密相关，除非录入错误，否则不存在修改的情况；
    2社会属性，与实体松散相关，会随着实际情况的变化而变化；3
    3系统属性，与实体基本无关，属于系统控制层次的属性
    
    对于确定的一个人，排除录入错误的情况，他的“出生日期”、“身份证号”是不会发生变化的，这就是一个人的自然属性；
    除此以外的其他属性都是社会属性，都有可能会发生变化。大家都知道目前国内改名的现象比较多，尽管公安机关的限制很严，依然挡不住改名的热潮。
    至于身高、体重、职务、婚姻状况，就更不用说了。
    护照一般都有有效期，过了有效期需要重新领新的护照，号码自然会发生变化。为什么“性别”也会变化呢？其实“性别”的取值范围是有国家标准的，取值分别是“男性”、“女性”、“未知的性别”、“未确定的性别”，感兴趣的朋友可以在网上搜搜。
    一般在录入人员信息的时候，如果不知道该人是男是女，默认应当选“未知的性别”，等以后知道了具体性别再作修正；此外男变女、女变男也不是什么新鲜事了，所以人的“性别”也是社会属性。    
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