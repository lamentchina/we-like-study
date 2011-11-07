create or replace function createGUID return varchar2 is
guid varchar(64); 
begin 
  guid := SYS_GUID(); 
  return 
  substr(guid,1,8)||'-'||substr(guid,9,4)|| 
  '-'||substr(guid,13,4)||'-'||substr(guid,17,4) 
  ||'-'||substr(guid,21,12); 
end createGUID;
/
