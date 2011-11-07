create or replace procedure init_module_user is
flag NUMBER;
prefix varchar(10);
begin
      flag := 0;
      prefix := init_prefix();
      --判断用户表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper(prefix||'member');
      if (flag<>0) then
         execute immediate 'drop table '||prefix||'MEMBER';
      end if;
      execute immediate '
      create table '||prefix||'member (
              keyid        int PRIMARY KEY
             ,username     varchar(200)
             ,password     varchar(200)
             ,remark       varchar(400)
             )
      ' ;
      
      --判断权限表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper(prefix||'permission');
      if (flag<>0) then
         execute immediate 'drop table '||prefix||'permission';
      end if;
      execute immediate '
      create table '||prefix||'permission (
              keyid        int PRIMARY KEY
             ,name         varchar(200)
             ,remark       varchar(400)
             )
      ' ;
      
      --判断权限表是否存在,存在则删除重建
      select count(*) into flag from all_tables where table_name=upper(prefix||'people');
      if (flag<>0) then
         execute immediate 'drop table '||prefix||'people';
      end if;
      execute immediate '
      create table '||prefix||'people (
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
