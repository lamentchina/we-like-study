--组织机构与权限设计
--  PURGE RECYCLEBIN   清空回收站

/*组织机构是每个公司都有的,是架构业务系统的基本
组织机构编码,国家设置了一个标准用来给每个企事业单位都设置一个长编号,唯一的编号
*/
create table nst_organization (
        id        varchar(200)        primary key --主键,使用 SYS-ID,可以产生一个全球唯一的编号,不知道数据库能否产生
       ,key       varchar(200)        unique      --组织机构编码,有层级关系的编码,可以组成树形结构
       ,name      varchar(200)        not null    --组织机构名称
       ,remark    varchar2(4000)                  --组织机构描述,就是一个简洁,可能要存储 HTML 字符串
)

/*人员表,用来描述现实世界中存在的个人单位
*/