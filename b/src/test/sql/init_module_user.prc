create or replace procedure init_module_user is
/**
  用户模块的数据表初始化
  包含的表有: 用户,权限,用户组,用户组-权限-多对多,用户-用户组-多对多,session表
  用户的权限,是通过用户组来判断的
  session表是内存表,系统关闭时自动关闭,系统启动时开启
 
  在业务层,用户相关的事务有:
  1 添加新员工(设置用户组,设置权限)
  2 员工辞职  (修改状态值)
  3 用户
 
  在业务层,用户组总共有：
  1 供应商,拥有 单据状态查询
  2 运维组,拥有所有权限
  3 部门领导,审批 否决 批注 
  4 部门员工,单据制单 单据提交 单据重改 单据作废 (其中 单据重改重点)
  
  (SCM中,单据主要有 
       需求单:说明我们要什么
       订单:  说明我们打算向哪些供应商买些什么
       库存单:说明仓库中存在些什么) 
       
  (一般在业务系统中产生的业务数据,都需要有的字段:
       创建时间 在数据库中生成的时间
       生效时间 此业务数据被系统正式启用的时间
       失效时间 此业务数据被系统停用的时间
       启用状态 描述此业务数据启用状态,有: 正在创建中,等待审批通过(以及审批到哪个流程),启用,停用审批(以及审批流程),停用
       描述     描述这个业务数据在流转过程中产生的任何需要详细说明的内容
       )
 **/
flag NUMBER;
begin
      flag := 0;
      --判断用户表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper('member');
      if (flag<>0) then
         execute immediate 'drop table MEMBER';
      end if;
      execute immediate '
      create table member (
              keyid        int PRIMARY KEY
             ,username     varchar(200)
             ,password     varchar(200)
             ,remark       varchar(400)
             )
      ' ;
      
      --判断权限表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper('permission');
      if (flag<>0) then
         execute immediate 'drop table permission';
      end if;
      execute immediate '
      create table permission (
              keyid        int PRIMARY KEY
             ,name         varchar(200)
             ,remark       varchar(400)
             )
      ' ;
      
      --判断权限表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper('people');
      if (flag<>0) then
         execute immediate 'drop table people';
      end if;
      execute immediate '
      create table people (
              keyid        int PRIMARY KEY
             ,realname     varchar(200)
             ,sex          int
             ,birthday     date
             ,degree       int
             ,marrage      int
             ,nationality  varchar(2)
             ,stock        int
             ,remark       varchar(400)
             )
      ' ;      
      
      execute immediate 'PURGE RECYCLEBIN';
end init_module_user;
/
