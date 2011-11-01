create or replace procedure data4test_module_po is
/*
 往订单表里插入随机的数据,用于测试开发
*/
flag NUMBER;
flag2 number;
ind number;
ind2 number;
begin

      flag := 0;
      select abs(mod(dbms_random.random,100)) into flag from dual;
      for ind in 1..flag loop
          insert into po_head (
                  keyid
                 ,po_no
                 ,price
                 ,id_user_created 
                 ,id_user_buyer
                 ,id_suppiler
                 ,id_contract
          ) values (
                  MYSEQ.NEXTVAL
                 ,ind
                 ,ind
                 ,ind
                 ,ind
                 ,ind
                 ,ind
          );
         commit;
         select abs(mod(dbms_random.random,100)) into flag2 from dual;
         for ind2 in 1..flag2 loop
            insert into po_line (
                     keyid
                    ,id_po_head
                    ,id_material
                    ,id_project
                    ,id_task
                    ,count
                    ,price
                    ,id_user_receiver
            ) values (
                    MDSYS.SAMPLE_SEQ.NEXTVAL
                   ,MYSEQ.Currval
                   ,ind2
                   ,ind2
                   ,ind2
                   ,ind2
                   ,ind2
                   ,ind2
            );
            commit;

          end loop; 
      end loop;      
      
end data4test_module_po;


