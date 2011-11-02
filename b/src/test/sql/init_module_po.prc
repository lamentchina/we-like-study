create or replace procedure init_module_po is
flag NUMBER;
begin

/**
 订单头
 
 所涉及的其他基础数据:
     物料,    在订单行中,描述所购买的物料名称
     任务点,  订单行中,描述这些物料将会被购买到哪个地方,用于在ERP那里结账用的
     项目,    订单行中用
     供应商,  订单头里,描述这个订单下的所有物料是从哪个供应商买来的
     合同,    订单头里用
 */
      flag := 0;
      select count(*) into flag from all_tables where table_name=upper('po_head');
      if (flag<>0) then
         execute immediate 'drop table po_head';
      end if;
      execute immediate ' 
      create table po_head (
           keyid            int PRIMARY KEY
          ,id_reference     int default 0 not null 
          ,po_no            varchar(200) not null
          ,price            number(16,6) default 0 not null       
          ,time_ordered     DATE default sysdate not null 
          ,time_created     DATE default sysdate not null 
          ,time_enable      DATE default to_date(''10000101'',''yyyymmdd'') not null 
          ,time_disable     DATE default to_date(''10000101'',''yyyymmdd'') not null           
          ,id_user_created  int not null 
          ,id_user_buyer    int not null 
          ,id_suppiler      int not null           
          ,id_contract      int not null  
          ,remark           varchar2(400) 
      )';
      execute immediate 'comment on table po_head is                        ''订单头'' '; 
      
      execute immediate 'comment on column po_head.po_no is                 ''订单号'' '; 
      execute immediate 'comment on column po_head.id_reference is          ''上级单据编号.一般情况下是 需求单,也有可能是提前到货订单'' ';
      execute immediate 'comment on column po_head.price is                 ''订单总金额'' '; 
      execute immediate 'comment on column po_head.time_ordered is          ''订购日期,供应商收到购买信息的时间'' '; 
      execute immediate 'comment on column po_head.time_created is          ''制单日期,数据库中生成这条记录的时间'' '; 
      execute immediate 'comment on column po_head.time_enable is           ''启用时间,这样就不用设置状态值了,只要这个值为1000-01-01就说明还未审核通过'' '; 
      execute immediate 'comment on column po_head.time_disable is          ''禁用时间,一般是审批失败导致作废,或者直接作废'' '; 
      execute immediate 'comment on column po_head.id_user_created is       ''制单人'' '; 
      execute immediate 'comment on column po_head.id_user_buyer is         ''采购员'' '; 
      execute immediate 'comment on column po_head.id_suppiler is           ''供应商'' '; 
      execute immediate 'comment on column po_head.id_contract is           ''合同'' '; 
      execute immediate 'comment on column po_head.remark is                ''批注'' ';
      

      
      flag := 0;
      select count(*) into flag from all_tables where table_name=upper('po_line');
      if (flag<>0) then
         execute immediate 'drop table po_line';
      end if;
      execute immediate ' 
        create table po_line (
             keyid            int PRIMARY KEY
            ,id_reference     int default 0 not null   
            ,id_po_head       int not null 
            ,id_material      int not null 
            ,id_project       int not null 
            ,id_task          int not null             
            ,count            int not null 
            ,price            number(16,6) default 0 not null 
            ,id_user_receiver int not null 
        )';
        
      execute immediate 'comment on table po_line is                        ''订单行'' '; 
      
      execute immediate 'comment on column po_line.id_reference is          ''订单行的业务来源一般是 需求单 ,或是提前到货订单'' '; 
      execute immediate 'comment on column po_line.id_po_head is            ''订单头   '' '; 
      execute immediate 'comment on column po_line.id_material is           ''物料'' '; 
      execute immediate 'comment on column po_line.id_project is            ''项目'' '; 
      execute immediate 'comment on column po_line.id_task is               ''任务点'' '; 
      execute immediate 'comment on column po_line.count is                 ''订购数量'' '; 
      execute immediate 'comment on column po_line.price is                 ''单价'' '; 
      execute immediate 'comment on column po_line.id_user_receiver is      ''接收人员'' '; 
      
      execute immediate 'PURGE RECYCLEBIN';
      
end init_module_po;
/
